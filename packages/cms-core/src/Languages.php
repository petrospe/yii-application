<?php

namespace sxoli\cms;

use sxoli\cms\models\Setting;
use yii\base\Application;

class Languages
{
    public const CODES = ['el', 'en'];

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'el' => 'Ελληνικά',
            'en' => 'English',
        ];
    }

    public static function isSupported(?string $code): bool
    {
        return is_string($code) && in_array($code, self::CODES, true);
    }

    public static function defaultCode(): string
    {
        try {
            $code = (string) Setting::get('language', 'el');
        } catch (\Throwable $e) {
            $code = 'el';
        }
        return self::isSupported($code) ? $code : 'el';
    }

    public static function locale(string $code): string
    {
        return $code === 'el' ? 'el_GR' : 'en_US';
    }

    public static function apply(Application $app, string $code): void
    {
        if (!self::isSupported($code)) {
            $code = 'el';
        }
        $app->language = $code;
        $app->formatter->locale = self::locale($code);
    }

    public static function isPublic(): bool
    {
        return \Yii::$app->id === 'app-frontend';
    }
}
