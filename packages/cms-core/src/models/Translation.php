<?php

namespace sxoli\cms\models;

use sxoli\cms\Languages;
use sxoli\cms\Sanitizer;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * @property int $id
 * @property string $entity
 * @property int $entity_id
 * @property string $language
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $excerpt
 * @property string|null $body
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_description
 */
class Translation extends ActiveRecord
{
    /** @var array<string, self|false> */
    private static array $cache = [];

    public static function tableName(): string
    {
        return '{{%cms_translation}}';
    }

    public function rules(): array
    {
        return [
            [['entity', 'entity_id', 'language'], 'required'],
            [['entity_id'], 'integer'],
            [['entity'], 'string', 'max' => 32],
            [['language'], 'string', 'max' => 5],
            [['title', 'slug', 'meta_title'], 'string', 'max' => 255],
            [['meta_description'], 'string', 'max' => 300],
            [['excerpt', 'body', 'description'], 'string'],
            [['slug'], 'validateSlug'],
        ];
    }

    public function validateSlug(string $attribute): void
    {
        $slug = (string) $this->slug;
        if ($slug === '' || !in_array($this->entity, ['content', 'term'], true)) {
            return;
        }
        if (in_array($slug, Content::RESERVED_SLUGS, true)) {
            $this->addError($attribute, \Yii::t('cms', 'This slug is reserved.'));
            return;
        }
        if ($this->entity === 'content') {
            $content = Content::findOne($this->entity_id);
            $type = $content->type ?? Content::TYPE_POST;
            $taken = Content::find()->where(['type' => $type, 'slug' => $slug])->exists();
            $query = static::find()->alias('t')
                ->innerJoin('{{%cms_content}} c', 'c.id = t.entity_id')
                ->where(['t.entity' => 'content', 't.slug' => $slug, 'c.type' => $type]);
            if (!$this->isNewRecord) {
                $query->andWhere(['<>', 't.id', $this->id]);
            }
            if ($taken || $query->exists()) {
                $this->addError($attribute, \Yii::t('cms', 'This slug is already used.'));
            }
            return;
        }
        $term = Term::findOne($this->entity_id);
        if ($term === null) {
            return;
        }
        $taken = Term::find()->where(['taxonomy_id' => $term->taxonomy_id, 'slug' => $slug])->exists();
        $query = static::find()->alias('t')
            ->innerJoin('{{%cms_term}} term', 'term.id = t.entity_id')
            ->where([
                't.entity' => 'term',
                't.language' => $this->language,
                't.slug' => $slug,
                'term.taxonomy_id' => $term->taxonomy_id,
            ]);
        if (!$this->isNewRecord) {
            $query->andWhere(['<>', 't.id', $this->id]);
        }
        if ($taken || $query->exists()) {
            $this->addError($attribute, \Yii::t('cms', 'This slug is already used.'));
        }
    }

    public static function value(string $entity, int $entityId, string $language, string $field): string
    {
        $row = self::row($entity, $entityId, $language);
        if ($row === null || !isset($row->$field)) {
            return '';
        }
        return trim((string) $row->$field);
    }

    /**
     * @param array<string, scalar|null> $fields
     */
    public static function store(string $entity, int $entityId, string $language, array $fields): ?string
    {
        $allowed = ['title', 'slug', 'excerpt', 'body', 'description', 'meta_title', 'meta_description'];
        $clean = [];
        foreach ($allowed as $name) {
            if (!array_key_exists($name, $fields)) {
                continue;
            }
            $clean[$name] = trim((string) $fields[$name]);
        }
        if (array_key_exists('slug', $clean)) {
            if ($clean['slug'] === '' && ($clean['title'] ?? '') !== '') {
                $clean['slug'] = Inflector::slug($clean['title']);
            } elseif ($clean['slug'] !== '') {
                $clean['slug'] = Inflector::slug($clean['slug']);
            }
        }
        $filled = array_filter($clean, static fn (string $value) => $value !== '');
        $model = static::findOne(['entity' => $entity, 'entity_id' => $entityId, 'language' => $language]);
        if ($filled === []) {
            if ($model !== null) {
                $model->delete();
            }
            self::forget($entity, $entityId, $language);
            return null;
        }
        if ($model === null) {
            $model = new static([
                'entity' => $entity,
                'entity_id' => $entityId,
                'language' => $language,
            ]);
        }
        foreach ($clean as $name => $value) {
            $model->$name = $value === '' ? null : $value;
        }
        if (!$model->save()) {
            return implode(' ', $model->getFirstErrors());
        }
        self::forget($entity, $entityId, $language);
        return null;
    }

    public static function storeRequest(string $entity, int $entityId, string $sourceLanguage): ?string
    {
        $posted = Yii::$app->request->post('Translation', []);
        if (!is_array($posted)) {
            return null;
        }
        $error = null;
        foreach ($posted as $language => $fields) {
            if (!Languages::isSupported((string) $language) || $language === $sourceLanguage || !is_array($fields)) {
                continue;
            }
            if (isset($fields['body'])) {
                $fields['body'] = Sanitizer::rich((string) $fields['body']);
            }
            $result = self::store($entity, $entityId, (string) $language, $fields);
            if ($result !== null) {
                $error = $result;
            }
        }
        return $error;
    }

    public static function remove(string $entity, int $entityId): void
    {
        static::deleteAll(['entity' => $entity, 'entity_id' => $entityId]);
        self::$cache = [];
    }

    private static function row(string $entity, int $entityId, string $language): ?self
    {
        $key = $entity . ':' . $entityId . ':' . $language;
        if (!array_key_exists($key, self::$cache)) {
            try {
                if (static::getTableSchema() === null) {
                    self::$cache[$key] = false;
                } else {
                    self::$cache[$key] = static::findOne([
                        'entity' => $entity,
                        'entity_id' => $entityId,
                        'language' => $language,
                    ]) ?: false;
                }
            } catch (\Throwable $e) {
                self::$cache[$key] = false;
            }
        }
        $row = self::$cache[$key];
        return $row instanceof self ? $row : null;
    }

    private static function forget(string $entity, int $entityId, string $language): void
    {
        unset(self::$cache[$entity . ':' . $entityId . ':' . $language]);
    }
}
