<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'user' => [
            'identityClass' => sxoli\cms\models\User::class,
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
            'loginUrl' => ['/site/login'],
        ],
        'session' => [
            'name' => 'sxoli-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'class' => sxoli\cms\web\UrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => (static function (): array {
                $plain = [
                    '' => 'content/index',
                    'blog' => 'content/blog',
                    'feed' => 'content/feed',
                    'search' => 'content/search',
                    'category/<slug:[\w\-]+>' => 'content/category',
                    'tag/<slug:[\w\-]+>' => 'content/tag',
                    'page/<slug:[\w\-]+>' => 'content/page',
                    '<year:\d{4}>/<month:\d{2}>/<slug:[\w\-]+>' => 'content/post',
                    'comment/create' => 'comment/create',
                    'login' => 'site/login',
                    'logout' => 'site/logout',
                    'signup' => 'site/signup',
                    'request-password-reset' => 'site/request-password-reset',
                    'reset-password/<token:[\w\-]+>' => 'site/reset-password',
                    'profile' => 'profile/index',
                    '<slug:[\w\-]+>' => 'content/resolve',
                ];
                $rules = [];
                foreach ($plain as $pattern => $route) {
                    $prefixed = $pattern === '' ? '<lang:(el|en)>' : '<lang:(el|en)>/' . $pattern;
                    $rules[$prefixed] = $route;
                }
                return $rules + $plain;
            })(),
        ],
        'view' => [
            'theme' => [
                'class' => yii\base\Theme::class,
                'pathMap' => [
                    '@frontend/views' => '@sxoli/theme/views',
                ],
            ],
        ],
    ],
    'params' => $params,
];
