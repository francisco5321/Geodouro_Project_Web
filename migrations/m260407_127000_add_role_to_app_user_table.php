<?php

use yii\db\Migration;

class m260407_127000_add_role_to_app_user_table extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%app_user}}', 'role', $this->string(32)->notNull()->defaultValue('user'));
        $this->createIndex('idx_app_user_role', '{{%app_user}}', 'role');
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx_app_user_role', '{{%app_user}}');
        $this->dropColumn('{{%app_user}}', 'role');
    }
}
