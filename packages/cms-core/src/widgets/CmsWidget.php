<?php

namespace sxoli\cms\widgets;

use yii\base\Widget;
use yii\helpers\Html;

abstract class CmsWidget extends Widget
{
    public string $title = '';
    public int $limit = 5;
    public string $body = '';
    public string $location = 'primary';
    public string $message = '';

    protected function wrap(string $html): string
    {
        $heading = $this->title !== ''
            ? Html::tag('h3', Html::encode($this->title), ['class' => 'widget-title'])
            : '';
        return Html::tag('section', $heading . $html, ['class' => 'widget']);
    }
}
