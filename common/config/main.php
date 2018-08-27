<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'extensions' => require(__DIR__ . '/../../vendor/yiisoft/extensions.php'),
    'modules' => [
        'social' => [
            // the module class
            'class' => 'kartik\social\Module',

            // the global settings for the disqus widget
            'disqus' => [
                'settings' => ['shortname' => 'DISQUS_SHORTNAME'] // default settings
            ],

            // the global settings for the facebook plugins widget
            'facebook' => [
                'appId' => '321082185128524', 
                'secret' => '608cca7c7a04cc6cbc0a94b80e388b35', 
            ],

            // the global settings for the google plugins widget
            'google' => [
                'clientId' => 'GOOGLE_API_CLIENT_ID',
                'pageId' => 'GOOGLE_PLUS_PAGE_ID',
                'profileId' => 'GOOGLE_PLUS_PROFILE_ID',
            ],

            // the global settings for the google analytic plugin widget
            'googleAnalytics' => [
                'id' => 'TRACKING_ID',
                'domain' => 'TRACKING_DOMAIN',
            ],

            // the global settings for the twitter plugin widget
            'twitter' => [
                'screenName' => 'TWITTER_SCREEN_NAME'
            ],
        ],
    // your other modules
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
    'id' => 'yii-application',
    'name'=>'Yii 2 Application',
];