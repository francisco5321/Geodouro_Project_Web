<?php

namespace app\models;

use yii\base\Model;

class ObservationForm extends Model
{
    public ?int $observation_id = null;
    public ?string $device_observation_id = null;
    public ?int $user_id = null;
    public ?int $plant_species_id = null;
    public ?string $image_uri = null;
    public ?int $captured_at = null;
    public ?string $predicted_scientific_name = null;
    public ?float $confidence = null;
    public ?string $enriched_scientific_name = null;
    public ?string $enriched_common_name = null;
    public ?string $enriched_family = null;
    public ?string $enriched_wikipedia_url = null;
    public ?string $enriched_photo_url = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?string $observed_at = null;
    public ?bool $is_published = null;
    public ?bool $is_synced = null;
    public ?string $sync_status = null;
    public ?int $last_sync_attempt_at = null;
    public ?string $notes = null;
    public bool $requires_manual_identification = false;
    public ?string $new_species_scientific_name = null;
    public ?string $new_species_common_name = null;
    public ?string $new_species_family = null;
    public ?string $new_species_genus = null;
    public ?string $new_species_species = null;
    public bool $isNewRecord = true;

    public function rules(): array
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'plant_species_id', 'captured_at', 'last_sync_attempt_at'], 'integer'],
            [['confidence', 'latitude', 'longitude'], 'number'],
            [['notes'], 'string'],
            [['is_published', 'is_synced', 'requires_manual_identification'], 'boolean'],
            [['observed_at'], 'safe'],
            [[
                'device_observation_id',
                'image_uri',
                'predicted_scientific_name',
                'enriched_scientific_name',
                'enriched_common_name',
                'enriched_family',
                'enriched_wikipedia_url',
                'enriched_photo_url',
                'sync_status',
                'new_species_scientific_name',
                'new_species_common_name',
                'new_species_family',
                'new_species_genus',
                'new_species_species',
            ], 'string'],
            [['confidence'], 'compare', 'compareValue' => 0, 'operator' => '>='],
            [['confidence'], 'compare', 'compareValue' => 1, 'operator' => '<='],
            [['latitude'], 'compare', 'compareValue' => -90, 'operator' => '>='],
            [['latitude'], 'compare', 'compareValue' => 90, 'operator' => '<='],
            [['longitude'], 'compare', 'compareValue' => -180, 'operator' => '>='],
            [['longitude'], 'compare', 'compareValue' => 180, 'operator' => '<='],
            [['sync_status'], 'in', 'range' => [
                Observation::SYNC_PENDING,
                Observation::SYNC_SYNCED,
                Observation::SYNC_FAILED,
                Observation::STATUS_MANUAL_REVIEW,
            ]],
            [['new_species_scientific_name', 'new_species_common_name', 'new_species_family', 'new_species_genus', 'new_species_species'], 'required', 'when' => fn (): bool => $this->isNewSpeciesRequested()],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'observation_id' => 'ID',
            'device_observation_id' => 'ID do Dispositivo',
            'user_id' => 'Utilizador',
            'plant_species_id' => 'Espécie',
            'image_uri' => 'Imagem Principal',
            'captured_at' => 'Capturada em Epoch',
            'predicted_scientific_name' => 'Nome Científico Previsto',
            'confidence' => 'Confiança',
            'enriched_scientific_name' => 'Nome Científico Enriquecido',
            'enriched_common_name' => 'Nome Comum Enriquecido',
            'enriched_family' => 'Família Enriquecida',
            'enriched_wikipedia_url' => 'Wikipedia',
            'enriched_photo_url' => 'Foto Enriquecida',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'observed_at' => 'Observada em',
            'is_published' => 'Publicada',
            'is_synced' => 'Sincronizada',
            'sync_status' => 'Estado de Sincronização',
            'last_sync_attempt_at' => 'Última Tentativa de Sync',
            'notes' => 'Notas',
            'new_species_scientific_name' => 'Nome Científico',
            'new_species_common_name' => 'Nome Comum',
            'new_species_family' => 'Família',
            'new_species_genus' => 'Género',
            'new_species_species' => 'Nome da Espécie',
        ];
    }

    public function isNewSpeciesRequested(): bool
    {
        return (int) $this->plant_species_id === Observation::NEW_SPECIES_SENTINEL;
    }

    public function needsManualReview(): bool
    {
        return $this->requires_manual_identification;
    }

    public function getResolvedScientificName(): ?string
    {
        return $this->enriched_scientific_name ?: $this->predicted_scientific_name;
    }
}
