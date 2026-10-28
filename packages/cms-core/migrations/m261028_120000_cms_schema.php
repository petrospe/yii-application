<?php

use yii\db\Expression;
use yii\db\Migration;

class m261028_120000_cms_schema extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $user = $this->db->getTableSchema('{{%user}}', true);
        if ($user !== null && !isset($user->columns['display_name'])) {
            $this->addColumn('{{%user}}', 'display_name', $this->string(255));
        }
        $user = $this->db->getTableSchema('{{%user}}', true);
        if ($user !== null && !isset($user->columns['bio'])) {
            $this->addColumn('{{%user}}', 'bio', $this->text());
        }
        if ($user !== null) {
            $this->update('{{%user}}', ['display_name' => new Expression('username')], ['display_name' => null]);
        }

        $this->createTable('{{%cms_setting}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'value' => $this->text(),
            'updated_at' => $this->dateTime(),
        ], $tableOptions);

        $this->createTable('{{%cms_taxonomy}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(64)->notNull()->unique(),
            'label' => $this->string(128)->notNull(),
            'hierarchical' => $this->boolean()->notNull()->defaultValue(false),
        ], $tableOptions);

        $this->createTable('{{%cms_term}}', [
            'id' => $this->primaryKey(),
            'taxonomy_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer(),
            'name' => $this->string(191)->notNull(),
            'slug' => $this->string(191)->notNull(),
            'description' => $this->text(),
            'count' => $this->integer()->notNull()->defaultValue(0),
        ], $tableOptions);
        $this->createIndex('ux_term_taxonomy_slug', '{{%cms_term}}', ['taxonomy_id', 'slug'], true);
        $this->addForeignKey('fk_term_taxonomy', '{{%cms_term}}', 'taxonomy_id', '{{%cms_taxonomy}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_term_parent', '{{%cms_term}}', 'parent_id', '{{%cms_term}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%cms_media}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'alt' => $this->string(255),
            'path' => $this->string(255)->notNull(),
            'mime' => $this->string(128)->notNull(),
            'size' => $this->integer()->notNull()->defaultValue(0),
            'width' => $this->integer(),
            'height' => $this->integer(),
            'author_id' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_media_author', '{{%cms_media}}', 'author_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%cms_content}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(32)->notNull(),
            'title' => $this->string(255)->notNull(),
            'slug' => $this->string(191)->notNull(),
            'excerpt' => $this->text(),
            'body' => $this->text(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'author_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer(),
            'featured_media_id' => $this->integer(),
            'comment_status' => $this->string(10)->notNull()->defaultValue('open'),
            'menu_order' => $this->integer()->notNull()->defaultValue(0),
            'meta_title' => $this->string(255),
            'meta_description' => $this->string(300),
            'published_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_content_type_slug', '{{%cms_content}}', ['type', 'slug'], true);
        $this->createIndex('ix_content_status_published', '{{%cms_content}}', ['type', 'status', 'published_at']);
        $this->addForeignKey('fk_content_author', '{{%cms_content}}', 'author_id', '{{%user}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_content_parent', '{{%cms_content}}', 'parent_id', '{{%cms_content}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_content_media', '{{%cms_content}}', 'featured_media_id', '{{%cms_media}}', 'id', 'SET NULL', 'CASCADE');
        if ($this->db->driverName === 'mysql') {
            $this->alterColumn('{{%cms_content}}', 'body', 'MEDIUMTEXT');
        }

        $this->createTable('{{%cms_content_revision}}', [
            'id' => $this->primaryKey(),
            'content_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'excerpt' => $this->text(),
            'body' => $this->text(),
            'author_id' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_revision_content', '{{%cms_content_revision}}', 'content_id', '{{%cms_content}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_revision_author', '{{%cms_content_revision}}', 'author_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        if ($this->db->driverName === 'mysql') {
            $this->alterColumn('{{%cms_content_revision}}', 'body', 'MEDIUMTEXT');
        }

        $this->createTable('{{%cms_content_term}}', [
            'content_id' => $this->integer()->notNull(),
            'term_id' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_content_term', '{{%cms_content_term}}', ['content_id', 'term_id']);
        $this->addForeignKey('fk_ct_content', '{{%cms_content_term}}', 'content_id', '{{%cms_content}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_ct_term', '{{%cms_content_term}}', 'term_id', '{{%cms_term}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%cms_menu}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'slug' => $this->string(128)->notNull()->unique(),
            'location' => $this->string(64),
        ], $tableOptions);
        $this->createIndex('ux_menu_location', '{{%cms_menu}}', 'location', true);

        $this->createTable('{{%cms_menu_item}}', [
            'id' => $this->primaryKey(),
            'menu_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer(),
            'label' => $this->string(255)->notNull(),
            'type' => $this->string(20)->notNull()->defaultValue('custom'),
            'url' => $this->string(255),
            'object_id' => $this->integer(),
            'target' => $this->string(10)->notNull()->defaultValue('_self'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ], $tableOptions);
        $this->addForeignKey('fk_item_menu', '{{%cms_menu_item}}', 'menu_id', '{{%cms_menu}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_item_parent', '{{%cms_menu_item}}', 'parent_id', '{{%cms_menu_item}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%cms_comment}}', [
            'id' => $this->primaryKey(),
            'content_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer(),
            'user_id' => $this->integer(),
            'author_name' => $this->string(128)->notNull(),
            'author_email' => $this->string(255)->notNull(),
            'author_url' => $this->string(255),
            'body' => $this->text()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'ip' => $this->string(45),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->createIndex('ix_comment_content_status', '{{%cms_comment}}', ['content_id', 'status']);
        $this->addForeignKey('fk_comment_content', '{{%cms_comment}}', 'content_id', '{{%cms_content}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_comment_parent', '{{%cms_comment}}', 'parent_id', '{{%cms_comment}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_comment_user', '{{%cms_comment}}', 'user_id', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%cms_widget}}', [
            'id' => $this->primaryKey(),
            'sidebar' => $this->string(64)->notNull(),
            'type' => $this->string(64)->notNull(),
            'title' => $this->string(255),
            'config' => $this->text(),
            'position' => $this->integer()->notNull()->defaultValue(0),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%cms_widget}}');
        $this->dropTable('{{%cms_comment}}');
        $this->dropTable('{{%cms_menu_item}}');
        $this->dropTable('{{%cms_menu}}');
        $this->dropTable('{{%cms_content_term}}');
        $this->dropTable('{{%cms_content_revision}}');
        $this->dropTable('{{%cms_content}}');
        $this->dropTable('{{%cms_media}}');
        $this->dropTable('{{%cms_term}}');
        $this->dropTable('{{%cms_taxonomy}}');
        $this->dropTable('{{%cms_setting}}');

        $user = $this->db->getTableSchema('{{%user}}', true);
        if ($user !== null && isset($user->columns['bio'])) {
            $this->dropColumn('{{%user}}', 'bio');
        }
        if ($user !== null && isset($user->columns['display_name'])) {
            $this->dropColumn('{{%user}}', 'display_name');
        }
    }
}
