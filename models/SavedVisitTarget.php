<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $saved_visit_target_id
 * @property int $user_id
 * @property int|null $plant_species_id
 * @property int|null $publication_id
 * @property int|null $observation_id
 * @property string|null $notes
 * @property string $created_at
 *
 * @property AppUser $user
 * @property PlantSpecies|null $plantSpecies
 * @property Publication|null $publication
 * @property Observation|null $observation
 * @property RoutePlanPoint[] $routePlanPoints
 */
class SavedVisitTarget extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%saved_visit_target}}';
    }

    public function rules(): array
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'plant_species_id', 'publication_id', 'observation_id'], 'integer'],
            [['notes'], 'string'],
            [['created_at'], 'safe'],
            [['user_id', 'plant_species_id'], 'unique', 'targetAttribute' => ['user_id', 'plant_species_id'], 'filter' => ['not', ['plant_species_id' => null]]],
            [['user_id', 'publication_id'], 'unique', 'targetAttribute' => ['user_id', 'publication_id'], 'filter' => ['not', ['publication_id' => null]]],
            [['user_id', 'observation_id'], 'unique', 'targetAttribute' => ['user_id', 'observation_id'], 'filter' => ['not', ['observation_id' => null]]],
            [['plant_species_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => PlantSpecies::class, 'targetAttribute' => ['plant_species_id' => 'plant_species_id']],
            [['publication_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => Publication::class, 'targetAttribute' => ['publication_id' => 'publication_id']],
            [['observation_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => Observation::class, 'targetAttribute' => ['observation_id' => 'observation_id']],
            [['user_id'], 'exist', 'targetClass' => AppUser::class, 'targetAttribute' => ['user_id' => 'user_id']],
            [['plant_species_id'], 'validateTargetReference'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'notes' => 'Notas do roteiro',
            'created_at' => 'Guardado em',
        ];
    }

    public function validateTargetReference(string $attribute, $params = null): void
    {
        if ($this->plant_species_id === null && $this->publication_id === null && $this->observation_id === null) {
            $this->addError($attribute, 'Seleciona pelo menos uma especie, publicacao ou observacao para visitar.');
        }
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(AppUser::class, ['user_id' => 'user_id']);
    }

    public function getPlantSpecies(): ActiveQuery
    {
        return $this->hasOne(PlantSpecies::class, ['plant_species_id' => 'plant_species_id']);
    }

    public function getPublication(): ActiveQuery
    {
        return $this->hasOne(Publication::class, ['publication_id' => 'publication_id']);
    }

    public function getObservation(): ActiveQuery
    {
        return $this->hasOne(Observation::class, ['observation_id' => 'observation_id']);
    }

    public function getRoutePlanPoints(): ActiveQuery
    {
        return $this->hasMany(RoutePlanPoint::class, ['saved_visit_target_id' => 'saved_visit_target_id']);
    }

    public function getTargetType(): string
    {
        if ($this->observation_id !== null) {
            return 'observation';
        }

        return $this->publication_id !== null ? 'publication' : 'species';
    }

    public function getTargetTitle(): string
    {
        if ($this->observation !== null) {
            return $this->observation->getResolvedCommonName() ?: 'Observacao botanica';
        }

        if ($this->publication !== null) {
            return $this->publication->title ?: ($this->publication->plantSpecies?->getDisplayName() ?? 'Publicacao botanica');
        }

        return $this->plantSpecies?->getDisplayName() ?? 'Especie selecionada';
    }

    public function getTargetSubtitle(): string
    {
        if ($this->observation !== null) {
            return $this->observation->getResolvedScientificName() ?: 'Observacao com coordenadas';
        }

        if ($this->publication !== null) {
            return $this->publication->plantSpecies?->scientific_name
                ?: ($this->publication->observation?->getResolvedScientificName() ?? 'Publicacao associada a observacao');
        }

        return $this->plantSpecies?->scientific_name ?? 'Sem classificacao cientifica';
    }

    public function getMapObservation(): ?Observation
    {
        if ($this->observation?->hasCoordinates()) {
            return $this->observation;
        }

        if ($this->publication?->observation?->hasCoordinates()) {
            return $this->publication->observation;
        }

        if ($this->plant_species_id === null) {
            return null;
        }

        return Observation::find()
            ->where(['plant_species_id' => $this->plant_species_id])
            ->andWhere(['not', ['latitude' => null]])
            ->andWhere(['not', ['longitude' => null]])
            ->orderBy(['observed_at' => SORT_DESC])
            ->one();
    }
}
