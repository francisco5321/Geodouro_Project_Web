<?php

use yii\db\Migration;

class m260407_123000_create_publication_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%publication}}', [
            'publication_id' => $this->primaryKey(),
            'observation_id' => $this->integer()->notNull()->unique(),
            'user_id' => $this->integer()->notNull(),
            'plant_species_id' => $this->integer(),
            'title' => $this->text(),
            'description' => $this->text(),
            'published_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('idx_publication_user_id', '{{%publication}}', 'user_id');
        $this->createIndex('idx_publication_plant_species_id', '{{%publication}}', 'plant_species_id');
        $this->createIndex('idx_publication_published_at', '{{%publication}}', 'published_at');

        $this->addForeignKey('fk_publication_observation_id', '{{%publication}}', 'observation_id', '{{%observation}}', 'observation_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_publication_user_id', '{{%publication}}', 'user_id', '{{%app_user}}', 'user_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_publication_plant_species_id', '{{%publication}}', 'plant_species_id', '{{%plant_species}}', 'plant_species_id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_publication_observation_id', '{{%publication}}');
        $this->dropForeignKey('fk_publication_user_id', '{{%publication}}');
        $this->dropForeignKey('fk_publication_plant_species_id', '{{%publication}}');
        $this->dropTable('{{%publication}}');
    }
}
