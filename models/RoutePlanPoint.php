<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $route_plan_point_id
 * @property int $route_plan_id
 * @property int $saved_visit_target_id
 * @property int $visit_order
 * @property string|null $notes
 * @property string $created_at
 *
 * @property RoutePlan $routePlan
 * @property SavedVisitTarget $savedVisitTarget
 */
class RoutePlanPoint extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%route_plan_point}}';
    }

    public function rules(): array
    {
        return [
            [['route_plan_id', 'saved_visit_target_id', 'visit_order'], 'required'],
            [['route_plan_id', 'saved_visit_target_id', 'visit_order'], 'integer'],
            [['notes'], 'string'],
            [['created_at'], 'safe'],
            [['route_plan_id', 'saved_visit_target_id'], 'unique', 'targetAttribute' => ['route_plan_id', 'saved_visit_target_id']],
            [['route_plan_id', 'visit_order'], 'unique', 'targetAttribute' => ['route_plan_id', 'visit_order']],
            [['route_plan_id'], 'exist', 'targetClass' => RoutePlan::class, 'targetAttribute' => ['route_plan_id' => 'route_plan_id']],
            [['saved_visit_target_id'], 'exist', 'targetClass' => SavedVisitTarget::class, 'targetAttribute' => ['saved_visit_target_id' => 'saved_visit_target_id']],
        ];
    }

    public function getRoutePlan(): ActiveQuery
    {
        return $this->hasOne(RoutePlan::class, ['route_plan_id' => 'route_plan_id']);
    }

    public function getSavedVisitTarget(): ActiveQuery
    {
        return $this->hasOne(SavedVisitTarget::class, ['saved_visit_target_id' => 'saved_visit_target_id']);
    }
}
