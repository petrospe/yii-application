<?php

namespace sxoli\cms\web;

use sxoli\cms\Languages;
use Yii;

class UrlManager extends \yii\web\UrlManager
{
    /**
     * @param array|string $params
     */
    public function createUrl($params)
    {
        if (is_array($params)) {
            $default = Languages::defaultCode();
            if (array_key_exists('lang', $params)) {
                if ($params['lang'] === null || $params['lang'] === '' || $params['lang'] === $default) {
                    unset($params['lang']);
                }
            } elseif (Languages::isSupported(Yii::$app->language) && Yii::$app->language !== $default) {
                $params['lang'] = Yii::$app->language;
            }
        }
        return parent::createUrl($params);
    }
}
