<?php

namespace sxoli\cms\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property int $hierarchical
 */
class Taxonomy extends ActiveRecord
{
    public const CATEGORY = 'category';
    public const TAG = 'post_tag';

    public static function tableName(): string
    {
        return '{{%cms_taxonomy}}';
    }

    public function getTerms(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Term::class, ['taxonomy_id' => 'id'])->orderBy(['name' => SORT_ASC]);
    }

    public static function findByName(string $name): ?self
    {
        return static::findOne(['name' => $name]);
    }
}
