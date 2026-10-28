<?php

namespace sxoli\cms\models;

use sxoli\cms\Languages;
use sxoli\cms\Sanitizer;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $type
 * @property string $language
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $body
 * @property string $status
 * @property int $author_id
 * @property int|null $parent_id
 * @property int|null $featured_media_id
 * @property string $comment_status
 * @property int $menu_order
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $published_at
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $author
 * @property self|null $parent
 * @property Media|null $featuredMedia
 * @property Term[] $terms
 * @property Comment[] $comments
 * @property ContentRevision[] $revisions
 */
class Content extends ActiveRecord
{
    public const TYPE_POST = 'post';
    public const TYPE_PAGE = 'page';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISH = 'publish';
    public const STATUS_PRIVATE = 'private';
    public const STATUS_TRASH = 'trash';

    public const RESERVED_SLUGS = [
        'search', 'category', 'tag', 'page', 'login', 'logout', 'signup', 'feed',
        'profile', 'blog', 'comment', 'debug', 'gii', 'uploads', 'request-password-reset',
        'el', 'en',
    ];

    /** @var int[]|string */
    public $categoryIds = [];

    public string $tagNames = '';

    public static function tableName(): string
    {
        return '{{%cms_content}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'type', 'status'], 'required'],
            ['language', 'in', 'range' => Languages::CODES],
            ['language', 'default', 'value' => 'el'],
            [['title', 'slug', 'meta_title'], 'string', 'max' => 255],
            [['meta_description'], 'string', 'max' => 300],
            [['excerpt', 'body', 'tagNames'], 'string'],
            [['parent_id', 'featured_media_id'], 'filter', 'filter' => static fn ($value) => $value === '' ? null : $value],
            [['author_id', 'parent_id', 'featured_media_id', 'menu_order'], 'integer'],
            ['type', 'in', 'range' => array_keys(Yii::$app->cms->postTypes())],
            ['status', 'in', 'range' => array_keys(self::statusLabels())],
            ['comment_status', 'in', 'range' => ['open', 'closed']],
            ['comment_status', 'default', 'value' => 'open'],
            ['published_at', 'safe'],
            ['categoryIds', 'each', 'rule' => ['integer']],
            ['parent_id', 'validateParent'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'language' => Yii::t('cms', 'Content language'),
            'title' => Yii::t('cms', 'Title'),
            'slug' => Yii::t('cms', 'Slug'),
            'excerpt' => Yii::t('cms', 'Excerpt'),
            'body' => Yii::t('cms', 'Content'),
            'status' => Yii::t('cms', 'Status'),
            'author_id' => Yii::t('cms', 'Author'),
            'parent_id' => Yii::t('cms', 'Parent'),
            'featured_media_id' => Yii::t('cms', 'Featured image'),
            'comment_status' => Yii::t('cms', 'Comments'),
            'menu_order' => Yii::t('cms', 'Order'),
            'meta_title' => Yii::t('cms', 'SEO title'),
            'meta_description' => Yii::t('cms', 'SEO description'),
            'published_at' => Yii::t('cms', 'Publish date'),
            'tagNames' => Yii::t('cms', 'Tags'),
            'categoryIds' => Yii::t('cms', 'Categories'),
            'created_at' => Yii::t('cms', 'Created'),
            'updated_at' => Yii::t('cms', 'Updated'),
        ];
    }

    public function validateParent(string $attribute): void
    {
        if (!$this->parent_id) {
            return;
        }
        if ((int) $this->parent_id === (int) $this->id) {
            $this->addError($attribute, Yii::t('cms', 'A page cannot be its own parent.'));
            return;
        }
        $parent = static::findOne($this->parent_id);
        if ($parent === null || $parent->type !== self::TYPE_PAGE) {
            $this->addError($attribute, Yii::t('cms', 'Choose a page as the parent.'));
        }
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if (!Languages::isSupported($this->language)) {
            $this->language = Languages::defaultCode();
        }
        if (!is_array($this->categoryIds)) {
            $this->categoryIds = $this->categoryIds === null || $this->categoryIds === '' ? [] : [(int) $this->categoryIds];
        }
        $source = trim((string) $this->slug) !== '' ? $this->slug : $this->title;
        $base = Inflector::slug((string) $source);
        if ($base === '') {
            $base = $this->type === self::TYPE_PAGE ? 'page' : 'post';
        }
        $slug = $base;
        $i = 2;
        while ($this->slugTaken($slug) && $i < 80) {
            $slug = $base . '-' . $i;
            $i++;
        }
        $this->slug = $slug;
        if ($this->published_at) {
            $stamp = str_replace('T', ' ', substr((string) $this->published_at, 0, 19));
            if (strlen($stamp) === 16) {
                $stamp .= ':00';
            }
            $this->published_at = $stamp;
        }
        return true;
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        if ($insert && !$this->created_at) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;
        $actor = $this->actorId();
        if (!$this->author_id && $actor !== null) {
            $this->author_id = $actor;
        }
        if ($this->excerpt === null || trim(strip_tags((string) $this->excerpt)) === '') {
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $this->body)) ?? '');
            $this->excerpt = mb_substr($plain, 0, 180);
        }
        $this->body = Sanitizer::rich((string) $this->body);
        if ($this->status === self::STATUS_PUBLISH && !$this->published_at) {
            $this->published_at = $now;
        }
        if ($this->type !== self::TYPE_PAGE) {
            $this->parent_id = null;
        }
        if ($this->published_at === '') {
            $this->published_at = null;
        }
        return true;
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $categoryIds = [];
        $tags = [];
        foreach ($this->terms as $term) {
            $taxonomy = $term->taxonomy->name ?? '';
            if ($taxonomy === Taxonomy::CATEGORY) {
                $categoryIds[] = (int) $term->id;
            } elseif ($taxonomy === Taxonomy::TAG) {
                $tags[] = $term->name;
            }
        }
        $this->categoryIds = $categoryIds;
        $this->tagNames = implode(', ', $tags);
    }

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);
        $watched = ['title', 'excerpt', 'body', 'status'];
        if ($insert || array_intersect(array_keys($changedAttributes), $watched)) {
            $revision = new ContentRevision([
                'content_id' => $this->id,
                'title' => $this->title,
                'excerpt' => $this->excerpt,
                'body' => $this->body,
                'author_id' => $this->actorId() ?? $this->author_id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $revision->save(false);
        }
        $this->syncTerms();
    }

    public function afterDelete(): void
    {
        Translation::remove('content', (int) $this->id);
        Term::refreshCounts();
        parent::afterDelete();
    }

    public function text(string $field): string
    {
        $value = trim((string) ($this->getAttribute($field) ?? ''));
        if (!Languages::isPublic()) {
            return $value;
        }
        $language = (string) Yii::$app->language;
        if ($language === ($this->language ?: Languages::defaultCode())) {
            return $value;
        }
        $translated = Translation::value('content', (int) $this->id, $language, $field);
        return $translated !== '' ? $translated : $value;
    }

    public static function findBySlug(string $type, string $slug): ?self
    {
        $language = (string) Yii::$app->language;
        $translated = Translation::findOne(['entity' => 'content', 'language' => $language, 'slug' => $slug]);
        if ($translated !== null) {
            $model = static::findOne(['id' => $translated->entity_id, 'type' => $type]);
            if ($model !== null && $model->language !== $language) {
                return $model;
            }
        }
        return static::findOne(['type' => $type, 'slug' => $slug]);
    }

    public static function findPublished(): \yii\db\ActiveQuery
    {
        return static::find()
            ->where(['status' => self::STATUS_PUBLISH])
            ->andWhere(['<=', 'published_at', date('Y-m-d H:i:s')]);
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => Yii::t('cms', 'Draft'),
            self::STATUS_PENDING => Yii::t('cms', 'Pending review'),
            self::STATUS_PUBLISH => Yii::t('cms', 'Published'),
            self::STATUS_PRIVATE => Yii::t('cms', 'Private'),
            self::STATUS_TRASH => Yii::t('cms', 'Trash'),
        ];
    }

    public function getAuthor(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    public function getParent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    public function getChildren(): \yii\db\ActiveQuery
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])->orderBy(['menu_order' => SORT_ASC, 'title' => SORT_ASC]);
    }

    public function getFeaturedMedia(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Media::class, ['id' => 'featured_media_id']);
    }

    public function getTerms(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Term::class, ['id' => 'term_id'])
            ->viaTable('{{%cms_content_term}}', ['content_id' => 'id']);
    }

    public function getComments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Comment::class, ['content_id' => 'id']);
    }

    public function getRevisions(): \yii\db\ActiveQuery
    {
        return $this->hasMany(ContentRevision::class, ['content_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public function getCategories(): array
    {
        return array_values(array_filter(
            $this->terms,
            static fn (Term $term) => ($term->taxonomy->name ?? '') === Taxonomy::CATEGORY
        ));
    }

    public function urlFor(string $language): string
    {
        $slug = (string) $this->slug;
        $source = $this->language ?: Languages::defaultCode();
        if ($language !== $source) {
            $translated = Translation::value('content', (int) $this->id, $language, 'slug');
            if ($translated !== '') {
                $slug = $translated;
            }
        }
        if ($this->type === self::TYPE_PAGE) {
            $path = 'page/' . $slug;
        } elseif (Setting::get('permalink_structure', 'postname') === 'dated' && $this->published_at) {
            $stamp = strtotime((string) $this->published_at) ?: time();
            $path = date('Y/m', $stamp) . '/' . $slug;
        } else {
            $path = $slug;
        }
        if ($language !== Languages::defaultCode()) {
            $path = $language . '/' . $path;
        }
        if (Yii::$app->id === 'app-frontend') {
            return Url::to('@web/' . $path);
        }
        return rtrim((string) Setting::get('site_url', ''), '/') . '/' . $path;
    }

    /** @return array<string, string> */
    public function languageUrls(): array
    {
        $urls = [];
        foreach (Languages::CODES as $code) {
            $urls[$code] = $this->urlFor($code);
        }
        return $urls;
    }

    public function getUrl(): string
    {
        if (Yii::$app->id === 'app-frontend') {
            return Url::to($this->routeParams());
        }
        $prefix = '';
        $language = $this->language ?: Languages::defaultCode();
        if ($language !== Languages::defaultCode()) {
            $prefix = $language . '/';
        }
        return rtrim((string) Setting::get('site_url', ''), '/') . '/' . $prefix . $this->getPath(false);
    }

    /** @return array<int|string, string> */
    public function routeParams(): array
    {
        $slug = $this->text('slug');
        if ($this->type === self::TYPE_PAGE) {
            return ['/content/page', 'slug' => $slug];
        }
        if (Setting::get('permalink_structure', 'postname') === 'dated' && $this->published_at) {
            $stamp = strtotime((string) $this->published_at) ?: time();
            return ['/content/post', 'year' => date('Y', $stamp), 'month' => date('m', $stamp), 'slug' => $slug];
        }
        return ['/content/resolve', 'slug' => $slug];
    }

    public function getPath(bool $localized = true): string
    {
        $slug = $localized ? $this->text('slug') : (string) $this->slug;
        if ($this->type === self::TYPE_PAGE) {
            return 'page/' . $slug;
        }
        if (Setting::get('permalink_structure', 'postname') === 'dated' && $this->published_at) {
            $stamp = strtotime((string) $this->published_at) ?: time();
            return date('Y/m', $stamp) . '/' . $slug;
        }
        return $slug;
    }

    public function renderedBody(): string
    {
        return (string) Yii::$app->cms->hooks->apply('cms.the_content', $this->text('body'), [
            'content' => $this,
        ]);
    }

    public function commentsOpen(): bool
    {
        return $this->comment_status === 'open' && Setting::get('comments_enabled', '1') === '1';
    }

    private function actorId(): ?int
    {
        if (!Yii::$app->has('user') || Yii::$app->user->isGuest) {
            return null;
        }
        return (int) Yii::$app->user->id;
    }

    private function slugTaken(string $slug): bool
    {
        if ($this->type !== self::TYPE_PAGE && in_array($slug, self::RESERVED_SLUGS, true)) {
            return true;
        }
        $type = $this->type ?: self::TYPE_POST;
        $query = static::find()->where(['type' => $type, 'slug' => $slug]);
        if (!$this->isNewRecord) {
            $query->andWhere(['<>', 'id', $this->id]);
        }
        if ($query->exists()) {
            return true;
        }
        $translations = Translation::find()->alias('t')
            ->innerJoin('{{%cms_content}} c', 'c.id = t.entity_id')
            ->where(['t.entity' => 'content', 't.slug' => $slug, 'c.type' => $type]);
        if (!$this->isNewRecord) {
            $translations->andWhere(['<>', 't.entity_id', $this->id]);
        }
        return $translations->exists();
    }

    private function syncTerms(): void
    {
        $ids = array_map('intval', $this->categoryIds);
        foreach (Term::fromNames($this->tagNames, Taxonomy::TAG) as $term) {
            $ids[] = (int) $term->id;
        }
        $ids = array_values(array_unique(array_filter($ids)));
        $db = static::getDb();
        $db->createCommand()->delete('{{%cms_content_term}}', ['content_id' => $this->id])->execute();
        foreach ($ids as $termId) {
            $db->createCommand()->insert('{{%cms_content_term}}', [
                'content_id' => $this->id,
                'term_id' => $termId,
            ])->execute();
        }
        Term::refreshCounts();
        $this->populateRelation('terms', Term::findAll($ids));
    }
}
