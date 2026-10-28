<?php

namespace sxoli\cms\models;

use sxoli\cms\Languages;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property int $id
 * @property string $name
 * @property string|null $value
 * @property string|null $updated_at
 */
class Setting extends ActiveRecord
{
    /** @var array<string, string|null>|null */
    private static ?array $cache = null;

    public static function tableName(): string
    {
        return '{{%cms_setting}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['value'], 'string'],
            [['name'], 'unique'],
        ];
    }

    public static function get(string $name, mixed $default = null): mixed
    {
        if (self::$cache === null) {
            self::$cache = self::loadAll();
        }
        return array_key_exists($name, self::$cache) ? self::$cache[$name] : $default;
    }

    public static function set(string $name, ?string $value): void
    {
        $model = static::findOne(['name' => $name]) ?? new static(['name' => $name]);
        $model->value = $value;
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save(false);
        self::$cache = null;
    }

    /** @param array<string, scalar|null> $pairs */
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $name => $value) {
            self::set((string) $name, $value === null ? null : (string) $value);
        }
    }

    public static function flush(): void
    {
        self::$cache = null;
    }

    public static function localized(string $name, string $default = ''): string
    {
        $value = (string) self::get($name, $default);
        if (!Languages::isPublic() || Yii::$app->language === Languages::defaultCode()) {
            return $value;
        }
        $row = static::findOne(['name' => $name]);
        if ($row === null) {
            return $value;
        }
        $translated = Translation::value('setting', (int) $row->id, (string) Yii::$app->language, 'title');
        return $translated !== '' ? $translated : $value;
    }

    /** @return array<string, string> */
    public static function texts(string $name): array
    {
        $raw = (string) self::get($name, '');
        $row = static::findOne(['name' => $name]);
        $texts = [];
        foreach (Languages::CODES as $code) {
            if ($code === Languages::defaultCode() || $row === null) {
                $texts[$code] = $code === Languages::defaultCode() ? $raw : '';
                continue;
            }
            $texts[$code] = Translation::value('setting', (int) $row->id, $code, 'title');
        }
        return $texts;
    }

    /** @return array<string, string|null> */
    private static function loadAll(): array
    {
        try {
            $schema = static::getDb()->getTableSchema(static::tableName(), true);
            if ($schema === null) {
                return [];
            }
            return ArrayHelper::map(static::find()->asArray()->all(), 'name', 'value');
        } catch (\Throwable $e) {
            Yii::warning($e->getMessage(), __METHOD__);
            return [];
        }
    }
}
