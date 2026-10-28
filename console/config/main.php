<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => yii\console\controllers\FixtureController::class,
            'namespace' => 'common\fixtures',
        ],
        'migrate' => [
            'class' => yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                '@console/migrations',
                '@sxoli/cms/migrations',
                '@yii/rbac/migrations',
            ],
        ],
        'cms' => [
            'class' => sxoli\cms\console\CmsController::class,
        ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
