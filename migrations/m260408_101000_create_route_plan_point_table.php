<?php

use yii\db\Migration;

class m260408_101000_create_route_plan_point_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%route_plan_point}}', [
            'route_plan_point_id' => $this->primaryKey(),
            'route_plan_id' => $this->integer()->notNull(),
            'saved_visit_target_id' => $this->integer()->notNull(),
            'visit_order' => $this->integer()->notNull(),
            'notes' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('idx_route_plan_point_route_plan_id', '{{%route_plan_point}}', 'route_plan_id');
        $this->createIndex('idx_route_plan_point_saved_visit_target_id', '{{%route_plan_point}}', 'saved_visit_target_id');
        $this->createIndex('ux_route_plan_point_unique_target', '{{%route_plan_point}}', ['route_plan_id', 'saved_visit_target_id'], true);
        $this->createIndex('ux_route_plan_point_visit_order', '{{%route_plan_point}}', ['route_plan_id', 'visit_order'], true);

        $this->addForeignKey('fk_route_plan_point_route_plan_id', '{{%route_plan_point}}', 'route_plan_id', '{{%route_plan}}', 'route_plan_id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_route_plan_point_saved_visit_target_id', '{{%route_plan_point}}', 'saved_visit_target_id', '{{%saved_visit_target}}', 'saved_visit_target_id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_route_plan_point_saved_visit_target_id', '{{%route_plan_point}}');
        $this->dropForeignKey('fk_route_plan_point_route_plan_id', '{{%route_plan_point}}');
        $this->dropTable('{{%route_plan_point}}');
    }
}
