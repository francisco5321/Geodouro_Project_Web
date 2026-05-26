<?php

namespace app\models;

use yii\base\Model;

class RoutePlanForm extends Model
{
    public ?int $route_plan_id = null;
    public ?int $user_id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $start_label = null;
    public ?float $start_latitude = null;
    public ?float $start_longitude = null;
    public bool $isNewRecord = true;

    public function rules(): array
    {
        return [
            [['user_id', 'name'], 'required'],
            [['user_id'], 'integer'],
            [['description'], 'string'],
            [['start_latitude', 'start_longitude'], 'number'],
            [['name', 'start_label'], 'string', 'max' => 255],
            [['start_label'], 'default', 'value' => null],
            [['start_latitude'], 'compare', 'compareValue' => -90, 'operator' => '>='],
            [['start_latitude'], 'compare', 'compareValue' => 90, 'operator' => '<='],
            [['start_longitude'], 'compare', 'compareValue' => -180, 'operator' => '>='],
            [['start_longitude'], 'compare', 'compareValue' => 180, 'operator' => '<='],
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
}
