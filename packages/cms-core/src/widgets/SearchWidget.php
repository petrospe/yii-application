<?php

namespace sxoli\cms\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class SearchWidget extends CmsWidget
{
    public function run(): string
    {
        $q = (string) Yii::$app->request->get('q', '');
        $form = Html::beginForm(Url::to(['/content/search']), 'get', ['class' => 'search-widget'])
            . Html::textInput('q', $q, [
                'placeholder' => Yii::t('cms', 'Search'),
                'aria-label' => Yii::t('cms', 'Search'),
            ])
            . Html::submitButton(Yii::t('cms', 'Search'))
            . Html::endForm();
        return $this->wrap($form);
    }
}
