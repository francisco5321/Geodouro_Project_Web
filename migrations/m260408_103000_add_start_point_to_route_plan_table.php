<?php

use yii\db\Migration;

class m260408_103000_add_start_point_to_route_plan_table extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%route_plan}}', 'start_label', $this->string()->null()->after('description'));
        $this->addColumn('{{%route_plan}}', 'start_latitude', $this->decimal(10, 7)->null()->after('start_label'));
        $this->addColumn('{{%route_plan}}', 'start_longitude', $this->decimal(10, 7)->null()->after('start_latitude'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%route_plan}}', 'start_longitude');
        $this->dropColumn('{{%route_plan}}', 'start_latitude');
        $this->dropColumn('{{%route_plan}}', 'start_label');
    }
}
