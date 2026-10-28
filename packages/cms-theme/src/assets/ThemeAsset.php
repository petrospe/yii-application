<?php

namespace sxoli\theme\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

class ThemeAsset extends AssetBundle
{
    public $sourcePath = __DIR__;
    public $css = ['css/theme.css'];
    public $depends = [YiiAsset::class];
}
