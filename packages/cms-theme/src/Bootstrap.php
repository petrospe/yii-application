<?php

namespace sxoli\theme;

use Yii;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        Yii::setAlias('@sxoli/theme', dirname(__DIR__));
    }
}
