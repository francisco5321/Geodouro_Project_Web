<?php

use yii\db\Migration;

class m260408_091000_create_saved_visit_target_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%saved_visit_target}}', [
            'saved_visit_target_id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'plant_species_id' => $this->integer(),
            'publication_id' => $this->integer(),
            'notes' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('idx_saved_visit_target_user_id', '{{%saved_visit_target}}', 'user_id');
        $this->createIndex('idx_saved_visit_target_species_id', '{{%saved_visit_target}}', 'plant_species_id');
        $this->createIndex('idx_saved_visit_target_publication_id', '{{%saved_visit_target}}', 'publication_id');
        $this->createIndex('ux_saved_visit_target_user_species', '{{%saved_visit_target}}', ['user_id', 'plant_species_id'], true);
        $this->createIndex('ux_saved_visit_target_user_publication', '{{%saved_visit_target}}', ['user_id', 'publication_id'], true);

        $this->addForeignKey('fk_saved_visit_target_user_id', '{{%saved_visit_target}}', 'user_id', '{{%app_user}}', 'user_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_saved_visit_target_species_id', '{{%saved_visit_target}}', 'plant_species_id', '{{%plant_species}}', 'plant_species_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_saved_visit_target_publication_id', '{{%saved_visit_target}}', 'publication_id', '{{%publication}}', 'publication_id', 'CASCADE', 'CASCADE');

        $this->execute("ALTER TABLE {{%saved_visit_target}} ADD CONSTRAINT chk_saved_visit_target_reference CHECK (plant_species_id IS NOT NULL OR publication_id IS NOT NULL)");
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_saved_visit_target_publication_id', '{{%saved_visit_target}}');
        $this->dropForeignKey('fk_saved_visit_target_species_id', '{{%saved_visit_target}}');
        $this->dropForeignKey('fk_saved_visit_target_user_id', '{{%saved_visit_target}}');
        $this->dropTable('{{%saved_visit_target}}');
    }
}
