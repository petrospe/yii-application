<?php

namespace sxoli\cms\widgets;

use sxoli\cms\models\Menu;
use yii\helpers\Html;

class MenuWidget extends CmsWidget
{
    public function run(): string
    {
        $menu = Menu::findByLocation($this->location) ?? Menu::findOne(['slug' => $this->location]);
        if ($menu === null) {
            return '';
        }
        return $this->wrap(self::renderItems($menu->tree()));
    }

    /** @param \sxoli\cms\models\MenuItem[] $items */
    public static function renderItems(array $items, string $class = 'menu'): string
    {
        if ($items === []) {
            return '';
        }
        $html = '';
        foreach ($items as $item) {
            $options = [];
            if ($item->target === '_blank') {
                $options['target'] = '_blank';
                $options['rel'] = 'noopener';
            }
            $link = Html::a(Html::encode($item->displayLabel()), $item->getHref(), $options);
            $children = $item->childrenCache !== [] ? self::renderItems($item->childrenCache, 'submenu') : '';
            $html .= Html::tag('li', $link . $children);
        }
        return Html::tag('ul', $html, ['class' => $class]);
    }
}
