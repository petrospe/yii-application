<?php

namespace sxoli\cms\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $sidebar
 * @property string $type
 * @property string|null $title
 * @property string|null $config
 * @property int $position
 * @property int $enabled
 */
class Widget extends ActiveRecord
{
    public $limit = 5;
    public string $body = '';
    public string $location = Menu::LOCATION_PRIMARY;
    public string $message = '';

    public static function tableName(): string
    {
        return '{{%cms_widget}}';
    }

    public function rules(): array
    {
        return [
            [['sidebar', 'type'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['sidebar', 'type', 'location'], 'string', 'max' => 64],
            [['body', 'message'], 'string'],
            [['position', 'enabled', 'limit'], 'integer'],
            ['enabled', 'default', 'value' => 1],
            ['sidebar', 'in', 'range' => array_keys(self::sidebars())],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => Yii::t('cms', 'Title'),
            'sidebar' => Yii::t('cms', 'Sidebar'),
            'type' => Yii::t('cms', 'Type'),
            'position' => Yii::t('cms', 'Order'),
            'enabled' => Yii::t('cms', 'Enabled'),
            'limit' => Yii::t('cms', 'Number of posts'),
            'body' => Yii::t('cms', 'Text'),
            'location' => Yii::t('cms', 'Menu location'),
            'message' => Yii::t('cms', 'Message'),
        ];
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $config = json_decode((string) $this->config, true);
        if (!is_array($config)) {
            return;
        }
        foreach (['limit', 'body', 'location', 'message'] as $key) {
            if (array_key_exists($key, $config)) {
                $this->$key = $config[$key];
            }
        }
        $this->limit = (int) $this->limit;
    }

    public function afterDelete(): void
    {
        Translation::remove('widget', (int) $this->id);
        parent::afterDelete();
    }

    public function beforeSave($insert): bool
    {
        $this->config = json_encode([
            'limit' => (int) $this->limit,
            'body' => (string) $this->body,
            'location' => (string) $this->location,
            'message' => (string) $this->message,
        ], JSON_UNESCAPED_UNICODE);
        return parent::beforeSave($insert);
    }

    /** @return array<string, string> */
    public static function sidebars(): array
    {
        return [
            'sidebar' => Yii::t('cms', 'Sidebar'),
            'footer' => Yii::t('cms', 'Footer'),
        ];
    }
}
