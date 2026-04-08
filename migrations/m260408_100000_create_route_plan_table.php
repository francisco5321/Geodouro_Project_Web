<?php

use yii\db\Migration;

class m260408_100000_create_route_plan_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%route_plan}}', [
            'route_plan_id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('idx_route_plan_user_id', '{{%route_plan}}', 'user_id');
        $this->addForeignKey('fk_route_plan_user_id', '{{%route_plan}}', 'user_id', '{{%app_user}}', 'user_id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_route_plan_user_id', '{{%route_plan}}');
        $this->dropTable('{{%route_plan}}');
    }
}
