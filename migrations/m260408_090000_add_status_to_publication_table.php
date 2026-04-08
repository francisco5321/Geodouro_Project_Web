<?php

use yii\db\Migration;

class m260408_090000_add_status_to_publication_table extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%publication}}', 'status', $this->string(32)->notNull()->defaultValue('published'));
        $this->addColumn('{{%publication}}', 'updated_at', $this->timestamp()->notNull()->defaultExpression('NOW()'));
        $this->createIndex('idx_publication_status', '{{%publication}}', 'status');
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx_publication_status', '{{%publication}}');
        $this->dropColumn('{{%publication}}', 'updated_at');
        $this->dropColumn('{{%publication}}', 'status');
    }
}
