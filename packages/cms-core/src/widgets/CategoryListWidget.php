<?php

namespace sxoli\cms\widgets;

use sxoli\cms\models\Term;
use yii\helpers\Html;

class CategoryListWidget extends CmsWidget
{
    public function run(): string
    {
        $terms = Term::forTaxonomy('category');
        if ($terms === []) {
            return '';
        }
        $items = '';
        foreach ($terms as $term) {
            $items .= Html::tag(
                'li',
                Html::a(Html::encode($term->text('name')), $term->getUrl()) . Html::tag('span', (string) $term->count, ['class' => 'count'])
            );
        }
        return $this->wrap(Html::tag('ul', $items, ['class' => 'widget-list']));
    }
}
