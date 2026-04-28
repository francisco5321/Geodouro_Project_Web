<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $route_plan_id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string|null $start_label
 * @property float|null $start_latitude
 * @property float|null $start_longitude
 * @property string $created_at
 * @property string $updated_at
 *
 * @property AppUser $user
 * @property RoutePlanPoint[] $routePlanPoints
 */
class RoutePlan extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%route_plan}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'name'], 'required'],
            [['user_id'], 'integer'],
            [['description'], 'string'],
            [['start_latitude', 'start_longitude'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'start_label'], 'string', 'max' => 255],
            [['start_latitude'], 'compare', 'compareValue' => -90, 'operator' => '>='],
            [['start_latitude'], 'compare', 'compareValue' => 90, 'operator' => '<='],
            [['start_longitude'], 'compare', 'compareValue' => -180, 'operator' => '>='],
            [['start_longitude'], 'compare', 'compareValue' => 180, 'operator' => '<='],
            [['start_label'], 'default', 'value' => null],
            [['user_id'], 'exist', 'targetClass' => AppUser::class, 'targetAttribute' => ['user_id' => 'user_id']],
            [['start_latitude', 'start_longitude'], 'validateStartPointPair'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Nome do percurso',
            'description' => 'Descrição',
            'start_label' => 'Nome do ponto de partida',
            'start_latitude' => 'Latitude do ponto de partida',
            'start_longitude' => 'Longitude do ponto de partida',
        ];
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        $this->start_label = trim((string) $this->start_label) ?: null;
        $this->start_latitude = $this->start_latitude === '' ? null : $this->start_latitude;
        $this->start_longitude = $this->start_longitude === '' ? null : $this->start_longitude;

        return true;
    }

    public function validateStartPointPair(string $attribute): void
    {
        if (($this->start_latitude === null) xor ($this->start_longitude === null)) {
            $this->addError('start_latitude', 'Define latitude e longitude para o ponto de partida.');
            $this->addError('start_longitude', 'Define latitude e longitude para o ponto de partida.');
        }
    }

    public function hasCustomStartPoint(): bool
    {
        return $this->start_latitude !== null && $this->start_longitude !== null;
    }

    public function getStartPoint(): ?array
    {
        if (!$this->hasCustomStartPoint()) {
            return null;
        }

        return [
            'label' => $this->start_label ?: 'Ponto de partida',
            'latitude' => (float) $this->start_latitude,
            'longitude' => (float) $this->start_longitude,
        ];
    }

    public function canBeManagedBy($user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->isAdmin() || (int) $user->user_id === (int) $this->user_id;
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(AppUser::class, ['user_id' => 'user_id']);
    }

    public function getRoutePlanPoints(): ActiveQuery
    {
        return $this->hasMany(RoutePlanPoint::class, ['route_plan_id' => 'route_plan_id'])->orderBy(['visit_order' => SORT_ASC, 'route_plan_point_id' => SORT_ASC]);
    }

    public function getPlannableTargets(): array
    {
        return [];
    }

    public function getPlannableSpecies(string $search = ''): array
    {
        return [];
    }

    public function getNextVisitOrder(): int
    {
        return 1;
    }
}
