<?php

namespace sxoli\cms\models;

use sxoli\cms\Languages;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $label
 * @property string $type
 * @property string|null $url
 * @property int|null $object_id
 * @property string $target
 * @property int $sort_order
 *
 * @property MenuItem[] $childrenCache
 */
class MenuItem extends ActiveRecord
{
    /** @var MenuItem[] */
    public array $childrenCache = [];

    public static function tableName(): string
    {
        return '{{%cms_menu_item}}';
    }

    public function rules(): array
    {
        return [
            [['menu_id', 'label', 'type'], 'required'],
            [['menu_id', 'parent_id', 'object_id', 'sort_order'], 'integer'],
            [['label', 'url'], 'string', 'max' => 255],
            ['type', 'in', 'range' => ['custom', 'content', 'term']],
            ['target', 'in', 'range' => ['_self', '_blank']],
            ['target', 'default', 'value' => '_self'],
            ['url', 'required', 'when' => static fn (self $model) => $model->type === 'custom'],
            ['object_id', 'required', 'when' => static fn (self $model) => $model->type !== 'custom'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'label' => Yii::t('cms', 'Label'),
            'type' => Yii::t('cms', 'Type'),
            'url' => Yii::t('cms', 'URL'),
            'object_id' => Yii::t('cms', 'Target'),
            'parent_id' => Yii::t('cms', 'Parent'),
            'target' => Yii::t('cms', 'Open in'),
        ];
    }

    public function afterDelete(): void
    {
        Translation::remove('menu_item', (int) $this->id);
        parent::afterDelete();
    }

    public function displayLabel(): string
    {
        if (!Languages::isPublic() || Yii::$app->language === Languages::defaultCode()) {
            return $this->label;
        }
        $translated = Translation::value('menu_item', (int) $this->id, (string) Yii::$app->language, 'title');
        return $translated !== '' ? $translated : $this->label;
    }

    public function getHref(): string
    {
        if ($this->type === 'content') {
            $content = Content::findOne($this->object_id);
            return $content ? $content->getUrl() : '#';
        }
        if ($this->type === 'term') {
            $term = Term::findOne($this->object_id);
            return $term ? $term->getUrl() : '#';
        }
        $url = (string) $this->url;
        if ($url === '') {
            return '#';
        }
        if ($url === '/') {
            return Yii::$app->id === 'app-frontend'
                ? \yii\helpers\Url::home()
                : rtrim((string) Setting::get('site_url', ''), '/') . '/';
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }
        return \yii\helpers\Url::to('@web/' . ltrim($url, '/'));
    }
}
