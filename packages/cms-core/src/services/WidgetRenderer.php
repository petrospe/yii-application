<?php

namespace sxoli\cms\services;

use sxoli\cms\Languages;
use sxoli\cms\models\Translation;
use sxoli\cms\models\Widget;
use Yii;

class WidgetRenderer
{
    public static function renderSidebar(string $sidebar): string
    {
        $html = '';
        $widgets = Widget::find()
            ->where(['sidebar' => $sidebar, 'enabled' => 1])
            ->orderBy(['position' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
        foreach ($widgets as $widget) {
            $type = Yii::$app->cms->widgetType($widget->type);
            if ($type === null) {
                continue;
            }
            $class = $type['class'];
            $html .= $class::widget([
                'title' => self::text($widget, 'title', (string) $widget->title),
                'limit' => (int) $widget->limit,
                'body' => self::text($widget, 'body', (string) $widget->body),
                'location' => (string) $widget->location,
                'message' => self::text($widget, 'excerpt', (string) $widget->message),
            ]);
        }
        return $html;
    }

    private static function text(Widget $widget, string $field, string $fallback): string
    {
        if (!Languages::isPublic() || Yii::$app->language === Languages::defaultCode()) {
            return $fallback;
        }
        $translated = Translation::value('widget', (int) $widget->id, (string) Yii::$app->language, $field);
        return $translated !== '' ? $translated : $fallback;
    }
}
