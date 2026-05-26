<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $plant_species_id
 * @property string $scientific_name
 * @property string|null $common_name
 * @property string $family
 * @property string $genus
 * @property string $species
 * @property int $image_count
 * @property string|null $description
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Observation[] $observations
 * @property Publication[] $publications
 * @property SavedVisitTarget[] $savedVisitTargets
 */
class PlantSpecies extends ActiveRecord
{
    public const SCENARIO_API_FORM = 'api-form';

    public static function tableName(): string
    {
        return '{{%plant_species}}';
    }

    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_API_FORM] = [
            'scientific_name',
            'common_name',
            'family',
            'genus',
            'species',
            'description',
            'image_count',
        ];

        return $scenarios;
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
            [['scientific_name', 'family', 'genus', 'species'], 'required'],
            [['description'], 'string'],
            [['image_count'], 'integer', 'min' => 0],
            [['created_at', 'updated_at'], 'safe'],
            [['scientific_name', 'common_name', 'family', 'genus', 'species'], 'string'],
            [['scientific_name'], 'unique', 'on' => [self::SCENARIO_DEFAULT]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'plant_species_id' => 'ID',
            'scientific_name' => 'Nome Científico',
            'common_name' => 'Nome Comum',
            'family' => 'Família',
            'genus' => 'Género',
            'species' => 'Espécie',
            'image_count' => 'Numero de Imagens',
            'description' => 'Descrição',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    public function getSpeciesSlug(): string
    {
        return strtolower(str_replace(' ', '_', trim($this->scientific_name)));
    }

    public function getDisplayName(): string
    {
        return $this->common_name ?: $this->scientific_name;
    }

    public function isSavedForUser($user): bool
    {
        return false;
    }

    public function getObservations(): ActiveQuery
    {
        return $this->hasMany(Observation::class, ['plant_species_id' => 'plant_species_id']);
    }

    public function getPublications(): ActiveQuery
    {
        return $this->hasMany(Publication::class, ['plant_species_id' => 'plant_species_id']);
    }

    public function getSavedVisitTargets(): ActiveQuery
    {
        return $this->hasMany(SavedVisitTarget::class, ['plant_species_id' => 'plant_species_id']);
    }
}
