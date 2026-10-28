<?php

namespace sxoli\cms\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $content_id
 * @property string $title
 * @property string|null $excerpt
 * @property string|null $body
 * @property int|null $author_id
 * @property string $created_at
 *
 * @property User|null $author
 */
class ContentRevision extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%cms_content_revision}}';
    }

    public function getAuthor(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }
}
