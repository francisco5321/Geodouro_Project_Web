<?php

use yii\db\Migration;

class m260407_120000_create_app_user_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%app_user}}', [
            'user_id' => $this->primaryKey(),
            'is_authenticated' => $this->boolean()->notNull()->defaultValue(false),
            'guest_label' => $this->text()->notNull()->unique(),
            'first_name' => $this->text(),
            'last_name' => $this->text(),
            'email' => $this->text()->unique(),
            'username' => $this->text()->unique(),
            'password_hash' => $this->text(),
            'auth_key' => $this->string(64),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('idx_app_user_email', '{{%app_user}}', 'email');
        $this->createIndex('idx_app_user_username', '{{%app_user}}', 'username');
        $this->addCheckConstraint('chk_guest_label_not_empty', '{{%app_user}}', "char_length(trim(guest_label)) > 0");
        $this->addCheckConstraint(
            'chk_authenticated_user_data',
            '{{%app_user}}',
            "(is_authenticated = FALSE) OR (is_authenticated = TRUE AND first_name IS NOT NULL AND last_name IS NOT NULL AND email IS NOT NULL AND username IS NOT NULL)"
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%app_user}}');
    }
}
