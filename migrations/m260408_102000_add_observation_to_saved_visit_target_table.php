<?php

use yii\db\Migration;

class m260408_102000_add_observation_to_saved_visit_target_table extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%saved_visit_target}}', 'observation_id', $this->integer());
        $this->createIndex('idx_saved_visit_target_observation_id', '{{%saved_visit_target}}', 'observation_id');
        $this->createIndex('ux_saved_visit_target_user_observation', '{{%saved_visit_target}}', ['user_id', 'observation_id'], true);
        $this->addForeignKey('fk_saved_visit_target_observation_id', '{{%saved_visit_target}}', 'observation_id', '{{%observation}}', 'observation_id', 'CASCADE', 'CASCADE');
        $this->execute('ALTER TABLE {{%saved_visit_target}} DROP CONSTRAINT IF EXISTS chk_saved_visit_target_reference');
        $this->execute("ALTER TABLE {{%saved_visit_target}} ADD CONSTRAINT chk_saved_visit_target_reference CHECK (plant_species_id IS NOT NULL OR publication_id IS NOT NULL OR observation_id IS NOT NULL)");
    }

    public function safeDown(): void
    {
        $this->execute('ALTER TABLE {{%saved_visit_target}} DROP CONSTRAINT IF EXISTS chk_saved_visit_target_reference');
        $this->execute("ALTER TABLE {{%saved_visit_target}} ADD CONSTRAINT chk_saved_visit_target_reference CHECK (plant_species_id IS NOT NULL OR publication_id IS NOT NULL)");
        $this->dropForeignKey('fk_saved_visit_target_observation_id', '{{%saved_visit_target}}');
        $this->dropIndex('ux_saved_visit_target_user_observation', '{{%saved_visit_target}}');
        $this->dropIndex('idx_saved_visit_target_observation_id', '{{%saved_visit_target}}');
        $this->dropColumn('{{%saved_visit_target}}', 'observation_id');
    }
}
