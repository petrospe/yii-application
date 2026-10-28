<?php

namespace sxoli\cms\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $location
 *
 * @property MenuItem[] $items
 */
class Menu extends ActiveRecord
{
    public const LOCATION_PRIMARY = 'primary';
    public const LOCATION_FOOTER = 'footer';

    public static function tableName(): string
    {
        return '{{%cms_menu}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name', 'slug'], 'string', 'max' => 128],
            ['location', 'in', 'range' => array_keys(self::locations()), 'skipOnEmpty' => true],
            ['slug', 'unique'],
            ['location', 'unique', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('cms', 'Name'),
            'slug' => Yii::t('cms', 'Slug'),
            'location' => Yii::t('cms', 'Location'),
        ];
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        if (!$this->slug) {
            $this->slug = Inflector::slug((string) $this->name);
        }
        if ($this->location === '') {
            $this->location = null;
        }
        return true;
    }

    public function getItems(): \yii\db\ActiveQuery
    {
        return $this->hasMany(MenuItem::class, ['menu_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /** @return MenuItem[] */
    public function tree(): array
    {
        $grouped = [];
        foreach ($this->items as $item) {
            $grouped[(int) $item->parent_id][] = $item;
        }
        $walk = function (int $parentId) use (&$walk, $grouped): array {
            $branch = [];
            foreach ($grouped[$parentId] ?? [] as $item) {
                $item->childrenCache = $walk((int) $item->id);
                $branch[] = $item;
            }
            return $branch;
        };
        return $walk(0);
    }

    public static function findByLocation(string $location): ?self
    {
        return static::findOne(['location' => $location]);
    }

    /** @return array<string, string> */
    public static function locations(): array
    {
        return [
            self::LOCATION_PRIMARY => Yii::t('cms', 'Primary menu'),
            self::LOCATION_FOOTER => Yii::t('cms', 'Footer menu'),
        ];
    }
}
