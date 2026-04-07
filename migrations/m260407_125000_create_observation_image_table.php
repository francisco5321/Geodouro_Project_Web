<?php

use yii\db\Migration;

class m260407_125000_create_observation_image_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%observation_image}}', [
            'observation_image_id' => $this->primaryKey(),
            'observation_id' => $this->integer()->notNull(),
            'image_path' => $this->text()->notNull(),
            'thumbnail_path' => $this->text(),
            'mime_type' => $this->text(),
            'file_size_bytes' => $this->bigInteger(),
            'width_px' => $this->integer(),
            'height_px' => $this->integer(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('idx_observation_image_observation_id', '{{%observation_image}}', 'observation_id');
        $this->addForeignKey('fk_observation_image_observation_id', '{{%observation_image}}', 'observation_id', '{{%observation}}', 'observation_id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_observation_image_observation_id', '{{%observation_image}}');
        $this->dropTable('{{%observation_image}}');
    }
}
