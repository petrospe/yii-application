<?php

namespace sxoli\cms\admin\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

class AdminAsset extends AssetBundle
{
    public $sourcePath = __DIR__;
    public $css = ['css/admin.css'];
    public $js = ['js/editor.js'];
    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
    ];
}
