<?php

namespace sxoli\cms\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $title
 * @property string|null $alt
 * @property string $path
 * @property string $mime
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property int|null $author_id
 * @property string $created_at
 *
 * @property User|null $author
 */
class Media extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%cms_media}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'path', 'mime'], 'required'],
            [['title', 'alt'], 'string', 'max' => 255],
            [['path'], 'string', 'max' => 255],
            [['mime'], 'string', 'max' => 128],
            [['size', 'width', 'height', 'author_id'], 'integer'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => Yii::t('cms', 'Title'),
            'alt' => Yii::t('cms', 'Alternative text'),
            'mime' => Yii::t('cms', 'Type'),
            'size' => Yii::t('cms', 'Size'),
            'created_at' => Yii::t('cms', 'Uploaded'),
        ];
    }

    public function getAuthor(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function getUrl(): string
    {
        $relative = '/uploads/' . ltrim((string) $this->path, '/');
        if (Yii::$app->id === 'app-frontend') {
            return Url::to('@web' . $relative);
        }
        return rtrim((string) Setting::get('site_url', ''), '/') . $relative;
    }

    public function afterDelete(): void
    {
        $file = Yii::getAlias('@uploads/' . $this->path);
        if (is_file($file)) {
            unlink($file);
        }
        parent::afterDelete();
    }
}
