<?php

namespace sxoli\cms\services;

use sxoli\cms\models\Content;
use sxoli\cms\models\MenuItem;
use sxoli\cms\models\Setting;
use sxoli\cms\models\Term;
use sxoli\cms\models\Translation;
use sxoli\cms\models\Widget;

/**
 * English copy for the starter records. Existing translations are left as they are.
 */
class TranslationSeeder
{
    public static function seed(): void
    {
        if (Translation::getTableSchema() === null) {
            return;
        }
        $page = Content::findOne(['type' => Content::TYPE_PAGE, 'slug' => 'schetika']);
        if ($page !== null) {
            self::once('content', (int) $page->id, [
                'title' => 'About',
                'slug' => 'about',
                'body' => '<p>This site runs on Sxoli CMS. From the admin you can write pages, posts, menus and comments.</p>',
            ]);
        }
        $post = Content::findOne(['type' => Content::TYPE_POST, 'slug' => 'kalos-erthate-sto-sxoli-cms']);
        if ($post !== null) {
            self::once('content', (int) $post->id, [
                'title' => 'Welcome to Sxoli CMS',
                'slug' => 'welcome-to-sxoli-cms',
                'excerpt' => 'This is the first post. You can edit it, delete it, or write something new.',
                'body' => '<p>This is the first post. You can edit it, delete it, or write something new.</p><p>Categories, tags, media and comments are ready.</p>',
            ]);
        }
        $category = Term::findOne(['slug' => 'genika']);
        if ($category !== null) {
            self::once('term', (int) $category->id, [
                'title' => 'General',
                'slug' => 'general',
                'description' => 'The default category.',
            ]);
        }
        foreach (MenuItem::find()->all() as $item) {
            $english = match ($item->label) {
                'Αρχική' => 'Home',
                'Σχετικά' => 'About',
                default => null,
            };
            if ($english !== null) {
                self::once('menu_item', (int) $item->id, ['title' => $english]);
            }
        }
        foreach (Widget::find()->all() as $widget) {
            $fields = match ($widget->title) {
                'Αναζήτηση' => ['title' => 'Search'],
                'Τελευταία άρθρα' => ['title' => 'Recent posts'],
                'Κατηγορίες' => ['title' => 'Categories'],
                'Sxoli' => [
                    'title' => 'Sxoli',
                    'body' => '<p>Built with Yii 2 as a monorepo: core, admin, theme and plugins.</p>',
                ],
                'Πρόσθετο' => [
                    'title' => 'Plugin',
                    'excerpt' => 'This block comes from packages/plugins/hello.',
                ],
                default => null,
            };
            if ($fields !== null) {
                self::once('widget', (int) $widget->id, $fields);
            }
        }
        $tagline = Setting::findOne(['name' => 'tagline']);
        if ($tagline !== null) {
            self::once('setting', (int) $tagline->id, ['title' => 'A CMS on Yii 2']);
        }
    }

    /** @param array<string, string> $fields */
    private static function once(string $entity, int $id, array $fields): void
    {
        if (Translation::find()->where(['entity' => $entity, 'entity_id' => $id, 'language' => 'en'])->exists()) {
            return;
        }
        Translation::store($entity, $id, 'en', $fields);
    }
}
