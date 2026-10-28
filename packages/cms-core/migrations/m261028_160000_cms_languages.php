<?php

use sxoli\cms\services\TranslationSeeder;
use yii\db\Migration;

class m261028_160000_cms_languages extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $content = $this->db->getTableSchema('{{%cms_content}}', true);
        if ($content !== null && !isset($content->columns['language'])) {
            $this->addColumn('{{%cms_content}}', 'language', $this->string(5)->notNull()->defaultValue('el')->after('type'));
            $this->update('{{%cms_content}}', ['language' => 'el']);
        }

        if ($this->db->getTableSchema('{{%cms_translation}}', true) === null) {
            $this->createTable('{{%cms_translation}}', [
                'id' => $this->primaryKey(),
                'entity' => $this->string(32)->notNull(),
                'entity_id' => $this->integer()->notNull(),
                'language' => $this->string(5)->notNull(),
                'title' => $this->string(255),
                'slug' => $this->string(191),
                'excerpt' => $this->text(),
                'body' => $this->text(),
                'description' => $this->text(),
                'meta_title' => $this->string(255),
                'meta_description' => $this->string(300),
            ], $tableOptions);
            $this->createIndex('ux_translation_entity', '{{%cms_translation}}', ['entity', 'entity_id', 'language'], true);
            $this->createIndex('ix_translation_slug', '{{%cms_translation}}', ['entity', 'language', 'slug']);
            if ($this->db->driverName === 'mysql') {
                $this->alterColumn('{{%cms_translation}}', 'body', 'MEDIUMTEXT');
            }
        }

        $this->db->getSchema()->refresh();
        TranslationSeeder::seed();
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema('{{%cms_translation}}', true) !== null) {
            $this->dropTable('{{%cms_translation}}');
        }
        $content = $this->db->getTableSchema('{{%cms_content}}', true);
        if ($content !== null && isset($content->columns['language'])) {
            $this->dropColumn('{{%cms_content}}', 'language');
        }
    }
}
