<?php

namespace sxoli\cms;

use sxoli\cms\models\Setting;
use sxoli\cms\widgets\CategoryListWidget;
use sxoli\cms\widgets\MenuWidget;
use sxoli\cms\widgets\RecentPostsWidget;
use sxoli\cms\widgets\SearchWidget;
use sxoli\cms\widgets\TextWidget;
use Yii;
use yii\base\Component;

/**
 * Registry for post types, widgets and plugins.
 *
 * @property-read Hooks $hooks
 */
class Cms extends Component
{
    public Hooks $hooks;

    /** @var array<string, array<string, mixed>> */
    private array $postTypes = [];

    /** @var array<string, array{class: class-string, label: string}> */
    private array $widgetTypes = [];

    public function init(): void
    {
        parent::init();
        $this->hooks = new Hooks();
        $this->registerPostType('post', [
            'label' => Yii::t('cms', 'Posts'),
            'hierarchical' => false,
        ]);
        $this->registerPostType('page', [
            'label' => Yii::t('cms', 'Pages'),
            'hierarchical' => true,
        ]);
        $this->registerWidgetType('search', SearchWidget::class, Yii::t('cms', 'Search'));
        $this->registerWidgetType('recent_posts', RecentPostsWidget::class, Yii::t('cms', 'Recent posts'));
        $this->registerWidgetType('categories', CategoryListWidget::class, Yii::t('cms', 'Categories'));
        $this->registerWidgetType('text', TextWidget::class, Yii::t('cms', 'Text'));
        $this->registerWidgetType('menu', MenuWidget::class, Yii::t('cms', 'Menu'));
        $this->loadPlugins();
    }

    public function registerPostType(string $name, array $config): void
    {
        $this->postTypes[$name] = $config;
    }

    /** @return array<string, array<string, mixed>> */
    public function postTypes(): array
    {
        return $this->postTypes;
    }

    /**
     * @param class-string $class
     */
    public function registerWidgetType(string $id, string $class, string $label): void
    {
        $this->widgetTypes[$id] = ['class' => $class, 'label' => $label];
    }

    /** @return array<string, array{class: class-string, label: string}> */
    public function widgetTypes(): array
    {
        return $this->widgetTypes;
    }

    /** @return array{class: class-string, label: string}|null */
    public function widgetType(string $id): ?array
    {
        return $this->widgetTypes[$id] ?? null;
    }

    private function loadPlugins(): void
    {
        $raw = Setting::get('active_plugins', '[]');
        $classes = json_decode((string) $raw, true);
        if (!is_array($classes)) {
            return;
        }
        foreach ($classes as $class) {
            if (!is_string($class) || !class_exists($class)) {
                continue;
            }
            $plugin = new $class();
            if ($plugin instanceof PluginInterface) {
                $plugin->bootstrap($this);
            }
        }
    }
}
