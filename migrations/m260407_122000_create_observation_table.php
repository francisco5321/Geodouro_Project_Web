<?php

use yii\db\Migration;

class m260407_122000_create_observation_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%observation}}', [
            'observation_id' => $this->primaryKey(),
            'device_observation_id' => 'UUID UNIQUE',
            'user_id' => $this->integer()->notNull(),
            'plant_species_id' => $this->integer(),
            'image_uri' => $this->text(),
            'captured_at' => $this->bigInteger(),
            'predicted_scientific_name' => $this->text(),
            'confidence' => $this->float(),
            'enriched_scientific_name' => $this->text(),
            'enriched_common_name' => $this->text(),
            'enriched_family' => $this->text(),
            'enriched_wikipedia_url' => $this->text(),
            'enriched_photo_url' => $this->text(),
            'latitude' => $this->decimal(10, 7),
            'longitude' => $this->decimal(10, 7),
            'observed_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'is_published' => $this->boolean()->notNull()->defaultValue(false),
            'is_synced' => $this->boolean()->notNull()->defaultValue(false),
            'sync_status' => $this->text()->notNull()->defaultValue('PENDING'),
            'last_sync_attempt_at' => $this->bigInteger(),
            'notes' => $this->text(),
        ]);

        $this->createIndex('idx_observation_user_id', '{{%observation}}', 'user_id');
        $this->createIndex('idx_observation_plant_species_id', '{{%observation}}', 'plant_species_id');
        $this->createIndex('idx_observation_is_published', '{{%observation}}', 'is_published');
        $this->createIndex('idx_observation_sync_status', '{{%observation}}', 'sync_status');
        $this->createIndex('idx_observation_last_sync_attempt_at', '{{%observation}}', 'last_sync_attempt_at');
        $this->createIndex('idx_observation_device_observation_id', '{{%observation}}', 'device_observation_id');
        $this->createIndex('idx_observation_observed_at', '{{%observation}}', 'observed_at');
        $this->execute('CREATE INDEX idx_observation_captured_at ON {{%observation}}(captured_at DESC)');
        $this->createIndex('idx_observation_lat_lon', '{{%observation}}', ['latitude', 'longitude']);

        $this->addForeignKey('fk_observation_user_id', '{{%observation}}', 'user_id', '{{%app_user}}', 'user_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_observation_plant_species_id', '{{%observation}}', 'plant_species_id', '{{%plant_species}}', 'plant_species_id', 'SET NULL', 'CASCADE');

        $this->addCheckConstraint('chk_confidence_range', '{{%observation}}', '(confidence IS NULL OR (confidence >= 0 AND confidence <= 1))');
        $this->addCheckConstraint('chk_latitude_range', '{{%observation}}', '(latitude IS NULL OR (latitude >= -90 AND latitude <= 90))');
        $this->addCheckConstraint('chk_longitude_range', '{{%observation}}', '(longitude IS NULL OR (longitude >= -180 AND longitude <= 180))');
        $this->addCheckConstraint('chk_sync_status', '{{%observation}}', "sync_status IN ('PENDING', 'SYNCED', 'FAILED')");
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_observation_user_id', '{{%observation}}');
        $this->dropForeignKey('fk_observation_plant_species_id', '{{%observation}}');
        $this->dropTable('{{%observation}}');
    }
}
