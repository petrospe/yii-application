<?php

namespace sxoli\cms\admin;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'sxoli\cms\admin\controllers';
    public $defaultRoute = 'dashboard/index';

    public function init(): void
    {
        parent::init();
        Yii::$app->language = Yii::$app->language ?: 'el';
    }
}
