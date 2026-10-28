<?php

namespace sxoli\plugins\hello;

use sxoli\cms\Cms;
use sxoli\cms\PluginInterface;

class Plugin implements PluginInterface
{
    public function id(): string
    {
        return 'hello';
    }

    public function bootstrap(Cms $cms): void
    {
        $cms->registerWidgetType('hello', HelloWidget::class, 'Hello');
    }
}
