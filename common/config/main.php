<?php

return [
    'id' => 'sxoli-cms',
    'name' => 'Sxoli CMS',
    'language' => 'el',
    'sourceLanguage' => 'en-US',
    'timeZone' => 'Europe/Athens',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'extensions' => require dirname(__DIR__, 2) . '/vendor/yiisoft/extensions.php',
    'components' => [
        'cache' => [
            'class' => yii\caching\FileCache::class,
        ],
        'authManager' => [
            'class' => yii\rbac\DbManager::class,
        ],
        'i18n' => [
            'translations' => [
                'cms*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@sxoli/cms/messages',
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],
        'cms' => [
            'class' => sxoli\cms\Cms::class,
        ],
    ],
];
