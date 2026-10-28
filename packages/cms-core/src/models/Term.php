<?php

namespace sxoli\cms\models;

use sxoli\cms\Languages;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\Inflector;

/**
 * @property int $id
 * @property int $taxonomy_id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $count
 *
 * @property Taxonomy $taxonomy
 */
class Term extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%cms_term}}';
    }

    public function rules(): array
    {
        return [
            [['taxonomy_id', 'name'], 'required'],
            [['taxonomy_id', 'parent_id', 'count'], 'integer'],
            [['description'], 'string'],
            [['name', 'slug'], 'string', 'max' => 191],
            ['slug', 'unique', 'targetAttribute' => ['taxonomy_id', 'slug']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('cms', 'Name'),
            'slug' => Yii::t('cms', 'Slug'),
            'description' => Yii::t('cms', 'Description'),
            'parent_id' => Yii::t('cms', 'Parent'),
        ];
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if (!$this->slug) {
            $this->slug = Inflector::slug((string) $this->name);
        } else {
            $this->slug = Inflector::slug((string) $this->slug);
        }
        if ($this->slug === '') {
            $this->slug = 'term';
        }
        return true;
    }

    public function getTaxonomy(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Taxonomy::class, ['id' => 'taxonomy_id']);
    }

    public function getParent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    public function getChildren(): \yii\db\ActiveQuery
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])->orderBy(['name' => SORT_ASC]);
    }

    public function afterDelete(): void
    {
        Translation::remove('term', (int) $this->id);
        parent::afterDelete();
    }

    public function text(string $field): string
    {
        $value = trim((string) ($this->getAttribute($field) ?? ''));
        if (!Languages::isPublic() || Yii::$app->language === Languages::defaultCode()) {
            return $value;
        }
        $column = match ($field) {
            'name' => 'title',
            'description' => 'description',
            default => 'slug',
        };
        $translated = Translation::value('term', (int) $this->id, (string) Yii::$app->language, $column);
        return $translated !== '' ? $translated : $value;
    }

    public static function findBySlug(int $taxonomyId, string $slug): ?self
    {
        $language = (string) Yii::$app->language;
        if ($language !== Languages::defaultCode()) {
            $translated = Translation::find()->alias('t')
                ->select(['t.id', 't.entity', 't.entity_id', 't.language', 't.slug'])
                ->innerJoin('{{%cms_term}} term', 'term.id = t.entity_id')
                ->where([
                    't.entity' => 'term',
                    't.language' => $language,
                    't.slug' => $slug,
                    'term.taxonomy_id' => $taxonomyId,
                ])
                ->one();
            if ($translated !== null) {
                $term = static::findOne($translated->entity_id);
                if ($term !== null) {
                    return $term;
                }
            }
        }
        return static::findOne(['taxonomy_id' => $taxonomyId, 'slug' => $slug]);
    }

    public function urlFor(string $language): string
    {
        $name = $this->taxonomy->name ?? Taxonomy::CATEGORY;
        $route = $name === Taxonomy::TAG ? 'tag' : 'category';
        $slug = (string) $this->slug;
        if ($language !== Languages::defaultCode()) {
            $translated = Translation::value('term', (int) $this->id, $language, 'slug');
            if ($translated !== '') {
                $slug = $translated;
            }
        }
        $path = $route . '/' . $slug;
        if ($language !== Languages::defaultCode()) {
            $path = $language . '/' . $path;
        }
        if (Yii::$app->id === 'app-frontend') {
            return \yii\helpers\Url::to('@web/' . $path);
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
        $name = $this->taxonomy->name ?? Taxonomy::CATEGORY;
        $route = $name === Taxonomy::TAG ? 'tag' : 'category';
        $slug = $this->text('slug');
        if (Yii::$app->id === 'app-frontend') {
            return \yii\helpers\Url::to(["/content/{$route}", 'slug' => $slug]);
        }
        return rtrim((string) Setting::get('site_url', ''), '/') . '/' . $route . '/' . $this->slug;
    }

    /** @return self[] */
    public static function forTaxonomy(string $taxonomyName): array
    {
        $taxonomy = Taxonomy::findByName($taxonomyName);
        if ($taxonomy === null) {
            return [];
        }
        return static::find()->where(['taxonomy_id' => $taxonomy->id])->orderBy(['name' => SORT_ASC])->all();
    }

    /** @return self[] */
    public static function fromNames(string $csv, string $taxonomyName): array
    {
        $taxonomy = Taxonomy::findByName($taxonomyName);
        if ($taxonomy === null) {
            return [];
        }
        $names = preg_split('/\s*,\s*/u', trim($csv), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $terms = [];
        foreach ($names as $name) {
            $slug = Inflector::slug($name);
            if ($slug === '') {
                continue;
            }
            $term = static::findOne(['taxonomy_id' => $taxonomy->id, 'slug' => $slug]);
            if ($term === null) {
                $term = new static([
                    'taxonomy_id' => $taxonomy->id,
                    'name' => $name,
                    'slug' => $slug,
                ]);
                $term->save(false);
            }
            $terms[] = $term;
        }
        return $terms;
    }

    public static function refreshCounts(): void
    {
        static::updateAll(['count' => 0]);
        $rows = (new Query())
            ->select(['ct.term_id', 'n' => 'COUNT(*)'])
            ->from(['ct' => '{{%cms_content_term}}'])
            ->innerJoin(['c' => '{{%cms_content}}'], 'c.id = ct.content_id')
            ->where(['c.status' => Content::STATUS_PUBLISH])
            ->groupBy(['ct.term_id'])
            ->all();
        foreach ($rows as $row) {
            static::updateAll(['count' => (int) $row['n']], ['id' => $row['term_id']]);
        }
    }
}
