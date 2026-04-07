<?php

use yii\db\Migration;

class m260407_121000_create_plant_species_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%plant_species}}', [
            'plant_species_id' => $this->primaryKey(),
            'scientific_name' => $this->text()->notNull()->unique(),
            'common_name' => $this->text(),
            'family' => $this->text()->notNull(),
            'genus' => $this->text()->notNull(),
            'species' => $this->text()->notNull(),
            'image_count' => $this->integer()->notNull()->defaultValue(0),
            'description' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('idx_plant_species_family', '{{%plant_species}}', 'family');
        $this->createIndex('idx_plant_species_genus', '{{%plant_species}}', 'genus');
        $this->createIndex('idx_plant_species_species', '{{%plant_species}}', 'species');
        $this->addCheckConstraint('chk_image_count_non_negative', '{{%plant_species}}', 'image_count >= 0');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%plant_species}}');
    }
}
