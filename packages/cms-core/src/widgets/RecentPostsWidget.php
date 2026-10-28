<?php

namespace sxoli\cms\widgets;

use sxoli\cms\models\Content;
use yii\helpers\Html;

class RecentPostsWidget extends CmsWidget
{
    public function run(): string
    {
        $posts = Content::findPublished()
            ->andWhere(['type' => Content::TYPE_POST])
            ->orderBy(['published_at' => SORT_DESC])
            ->limit(max(1, $this->limit))
            ->all();
        if ($posts === []) {
            return '';
        }
        $items = '';
        foreach ($posts as $post) {
            $items .= Html::tag('li', Html::a(Html::encode($post->text('title')), $post->getUrl()));
        }
        return $this->wrap(Html::tag('ul', $items, ['class' => 'widget-list']));
    }
}
