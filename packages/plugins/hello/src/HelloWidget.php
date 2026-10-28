<?php

namespace sxoli\plugins\hello;

use sxoli\cms\widgets\CmsWidget;
use yii\helpers\Html;

class HelloWidget extends CmsWidget
{
    public function run(): string
    {
        $text = $this->message !== ''
            ? $this->message
            : 'Hello from the hello plugin.';
        return $this->wrap(Html::tag('p', Html::encode($text)));
    }
}
