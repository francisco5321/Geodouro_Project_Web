<?php

use yii\db\Migration;

class m260407_126000_create_publication_image_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%publication_image}}', [
            'publication_image_id' => $this->primaryKey(),
            'publication_id' => $this->integer()->notNull(),
            'image_path' => $this->text()->notNull(),
            'thumbnail_path' => $this->text(),
            'mime_type' => $this->text(),
            'file_size_bytes' => $this->bigInteger(),
            'width_px' => $this->integer(),
            'height_px' => $this->integer(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('idx_publication_image_publication_id', '{{%publication_image}}', 'publication_id');
        $this->addForeignKey('fk_publication_image_publication_id', '{{%publication_image}}', 'publication_id', '{{%publication}}', 'publication_id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_publication_image_publication_id', '{{%publication_image}}');
        $this->dropTable('{{%publication_image}}');
    }
}
