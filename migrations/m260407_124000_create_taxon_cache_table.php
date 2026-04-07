<?php

use yii\db\Migration;

class m260407_124000_create_taxon_cache_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%taxon_cache}}', [
            'species_query' => $this->text()->notNull(),
            'taxon_id' => $this->bigInteger(),
            'scientific_name' => $this->text()->notNull(),
            'common_name' => $this->text(),
            'family' => $this->text(),
            'wikipedia_url' => $this->text(),
            'photo_url' => $this->text(),
            'updated_at' => $this->bigInteger()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'PRIMARY KEY(species_query)',
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%taxon_cache}}');
    }
}
