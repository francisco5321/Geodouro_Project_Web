<?php

use yii\db\Migration;

class m260422_120000_add_performance_indexes extends Migration
{
    public function safeUp(): void
    {
        $this->createIndex(
            'idx_observation_user_observed_id',
            '{{%observation}}',
            ['user_id', 'observed_at', 'observation_id']
        );
        $this->createIndex(
            'idx_observation_species_observed_id',
            '{{%observation}}',
            ['plant_species_id', 'observed_at', 'observation_id']
        );
        $this->createIndex(
            'idx_publication_user_published_id',
            '{{%publication}}',
            ['user_id', 'published_at', 'publication_id']
        );
        $this->createIndex(
            'idx_route_plan_user_updated_id',
            '{{%route_plan}}',
            ['user_id', 'updated_at', 'route_plan_id']
        );
        $this->createIndex(
            'idx_saved_visit_target_user_created_id',
            '{{%saved_visit_target}}',
            ['user_id', 'created_at', 'saved_visit_target_id']
        );
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx_saved_visit_target_user_created_id', '{{%saved_visit_target}}');
        $this->dropIndex('idx_route_plan_user_updated_id', '{{%route_plan}}');
        $this->dropIndex('idx_publication_user_published_id', '{{%publication}}');
        $this->dropIndex('idx_observation_species_observed_id', '{{%observation}}');
        $this->dropIndex('idx_observation_user_observed_id', '{{%observation}}');
    }
}
