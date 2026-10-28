<?php

namespace sxoli\cms\console;

use sxoli\cms\models\Content;
use sxoli\cms\models\Menu;
use sxoli\cms\models\MenuItem;
use sxoli\cms\models\Setting;
use sxoli\cms\models\Taxonomy;
use sxoli\cms\models\Term;
use sxoli\cms\models\User;
use sxoli\cms\models\Widget;
use sxoli\cms\services\RbacSeeder;
use sxoli\cms\services\TranslationSeeder;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\FileHelper;

class CmsController extends Controller
{
    /**
     * Create the CMS tables, roles, default settings and starter content.
     */
    public function actionInstall(): int
    {
        $this->stdout("Applying migrations...\n");
        Yii::$app->runAction('migrate/up', ['interactive' => 0]);

        $this->stdout("Seeding roles...\n");
        (new RbacSeeder())->seed();
        $this->seedSettings();
        $this->seedTaxonomies();
        $password = $this->ensureOperator();
        $this->seedContent();
        $this->seedMenus();
        $this->seedWidgets();
        TranslationSeeder::seed();
        FileHelper::createDirectory(Yii::getAlias('@uploads'));

        $this->stdout("\nSxoli CMS is ready.\n");
        $this->stdout('Site:  ' . Setting::get('site_url') . "\n");
        $this->stdout('Admin: ' . Setting::get('admin_url') . "\n");
        if ($password !== null) {
            $this->stdout("New operator account\n  username: cms\n  password: {$password}\n");
        } else {
            $this->stdout("Existing users kept their passwords. Administrators can sign in with their current password.\n");
        }
        return ExitCode::OK;
    }

    /**
     * Set a user's password: php yii cms/password <username> <password>
     */
    public function actionPassword(string $username, string $password): int
    {
        $user = User::findOne(['username' => $username]);
        if ($user === null) {
            $this->stderr("User not found.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        if (strlen($password) < 8) {
            $this->stderr("Use at least 8 characters.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $user->setPassword($password);
        $user->save(false);
        $this->stdout("Password updated for {$username}.\n");
        return ExitCode::OK;
    }

    private function seedSettings(): void
    {
        $defaults = [
            'site_name' => 'Sxoli CMS',
            'tagline' => 'Ένα CMS σε Yii 2',
            'site_url' => Yii::$app->params['frontendUrl'] ?? 'http://127.0.0.1:8080',
            'admin_url' => Yii::$app->params['backendUrl'] ?? 'http://127.0.0.1:8081',
            'admin_email' => Yii::$app->params['adminEmail'] ?? 'admin@example.com',
            'language' => 'el',
            'timezone' => 'Europe/Athens',
            'posts_per_page' => '10',
            'show_on_front' => 'posts',
            'page_on_front' => '',
            'comments_enabled' => '1',
            'comment_moderation' => '1',
            'comment_registration' => '0',
            'permalink_structure' => 'postname',
            'active_plugins' => json_encode(['sxoli\\plugins\\hello\\Plugin'], JSON_UNESCAPED_UNICODE),
        ];
        foreach ($defaults as $name => $value) {
            if (Setting::findOne(['name' => $name]) === null) {
                Setting::set($name, $value);
            }
        }
    }

    private function seedTaxonomies(): void
    {
        if (Taxonomy::findByName(Taxonomy::CATEGORY) === null) {
            $category = new Taxonomy(['name' => Taxonomy::CATEGORY, 'label' => 'Κατηγορίες', 'hierarchical' => 1]);
            $category->save(false);
        }
        if (Taxonomy::findByName(Taxonomy::TAG) === null) {
            $tag = new Taxonomy(['name' => Taxonomy::TAG, 'label' => 'Ετικέτες', 'hierarchical' => 0]);
            $tag->save(false);
        }
        $taxonomy = Taxonomy::findByName(Taxonomy::CATEGORY);
        if ($taxonomy !== null && !Term::find()->where(['taxonomy_id' => $taxonomy->id])->exists()) {
            $term = new Term([
                'taxonomy_id' => $taxonomy->id,
                'name' => 'Γενικά',
                'slug' => 'genika',
                'description' => 'Η προεπιλεγμένη κατηγορία.',
            ]);
            $term->save(false);
        }
    }

    private function ensureOperator(): ?string
    {
        if (User::find()->where(['username' => 'cms'])->exists()) {
            $user = User::findOne(['username' => 'cms']);
            if ($user !== null && !in_array('admin', $user->getRoleNames(), true)) {
                $user->assignRole('admin');
            }
            return null;
        }
        $password = 'Cms-' . substr(bin2hex(random_bytes(4)), 0, 8);
        $user = new User(['scenario' => 'create']);
        $user->username = 'cms';
        $user->email = 'cms@example.com';
        $user->display_name = 'Διαχειριστής';
        $user->password = $password;
        $user->role = 'admin';
        $user->status_id = User::STATUS_ACTIVE;
        if (!$user->save()) {
            $this->stderr("Could not create the operator account.\n");
            return null;
        }
        $user->assignRole('admin');
        return $password;
    }

    private function seedContent(): void
    {
        $author = User::findOne(['username' => 'cms']) ?? User::find()->orderBy(['id' => SORT_ASC])->one();
        if ($author === null) {
            return;
        }
        $category = Term::findOne(['slug' => 'genika']);

        $page = Content::findOne(['type' => Content::TYPE_PAGE, 'slug' => 'schetika']);
        if ($page === null) {
            $page = new Content();
            $page->type = Content::TYPE_PAGE;
            $page->title = 'Σχετικά';
            $page->body = '<p>Αυτός ο ιστότοπος τρέχει με το Sxoli CMS. Από τη διαχείριση μπορείς να γράψεις σελίδες, άρθρα, μενού και σχόλια.</p>';
            $page->status = Content::STATUS_PUBLISH;
            $page->comment_status = 'closed';
            $page->author_id = (int) $author->id;
            $page->menu_order = 1;
            if (!$page->save()) {
                $this->stderr(json_encode($page->errors, JSON_UNESCAPED_UNICODE) . "\n");
            }
        }

        if (!Content::find()->where(['type' => Content::TYPE_POST])->exists()) {
            $post = new Content();
            $post->type = Content::TYPE_POST;
            $post->title = 'Καλώς ήρθατε στο Sxoli CMS';
            $post->body = '<p>Αυτό είναι το πρώτο άρθρο. Μπορείς να το επεξεργαστείς, να το σβήσεις ή να γράψεις κάτι καινούργιο.</p><p>Οι κατηγορίες, οι ετικέτες, τα πολυμέσα και τα σχόλια είναι έτοιμα.</p>';
            $post->status = Content::STATUS_PUBLISH;
            $post->comment_status = 'open';
            $post->author_id = (int) $author->id;
            if ($category !== null) {
                $post->categoryIds = [(int) $category->id];
            }
            $post->tagNames = 'cms, yii';
            if (!$post->save()) {
                $this->stderr(json_encode($post->errors, JSON_UNESCAPED_UNICODE) . "\n");
            }
        }

        if ($page !== null && $page->id && Setting::get('page_on_front', '') === '') {
            Setting::set('page_on_front', (string) $page->id);
        }
    }

    private function seedMenus(): void
    {
        if (Menu::find()->exists()) {
            return;
        }
        $page = Content::findOne(['type' => Content::TYPE_PAGE, 'slug' => 'schetika'])
            ?? Content::findOne(['type' => Content::TYPE_PAGE]);

        $primary = new Menu(['name' => 'Κύριο', 'slug' => 'primary', 'location' => Menu::LOCATION_PRIMARY]);
        $primary->save(false);
        $this->item($primary, 'Αρχική', 'custom', '/', 0);
        if ($page !== null) {
            $this->item($primary, $page->title, 'content', null, 1, (int) $page->id);
        }

        $footer = new Menu(['name' => 'Υποσέλιδο', 'slug' => 'footer', 'location' => Menu::LOCATION_FOOTER]);
        $footer->save(false);
        $this->item($footer, 'Αρχική', 'custom', '/', 0);
    }

    private function item(Menu $menu, string $label, string $type, ?string $url, int $order, ?int $objectId = null): void
    {
        $item = new MenuItem([
            'menu_id' => $menu->id,
            'label' => $label,
            'type' => $type,
            'url' => $url,
            'object_id' => $objectId,
            'sort_order' => $order,
            'target' => '_self',
        ]);
        $item->save(false);
    }

    private function seedWidgets(): void
    {
        if (Widget::find()->exists()) {
            return;
        }
        $this->widget('sidebar', 'search', 'Αναζήτηση', 0);
        $this->widget('sidebar', 'recent_posts', 'Τελευταία άρθρα', 1, ['limit' => 5]);
        $this->widget('sidebar', 'categories', 'Κατηγορίες', 2);
        $this->widget('footer', 'text', 'Sxoli', 0, [
            'body' => '<p>Φτιαγμένο με Yii 2, ως monorepo: πυρήνας, διαχείριση, θέμα και πρόσθετα.</p>',
        ]);
        $this->widget('footer', 'hello', 'Πρόσθετο', 1, [
            'message' => 'Αυτό το μπλοκ έρχεται από το packages/plugins/hello.',
        ]);
    }

    /** @param array<string, mixed> $config */
    private function widget(string $sidebar, string $type, string $title, int $position, array $config = []): void
    {
        $widget = new Widget([
            'sidebar' => $sidebar,
            'type' => $type,
            'title' => $title,
            'position' => $position,
            'enabled' => 1,
        ]);
        foreach ($config as $key => $value) {
            $widget->$key = $value;
        }
        $widget->save(false);
    }
}
