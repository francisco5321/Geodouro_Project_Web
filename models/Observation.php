<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $observation_id
 * @property string|null $device_observation_id
 * @property int $user_id
 * @property int|null $plant_species_id
 * @property string|null $image_uri
 * @property int|null $captured_at
 * @property string|null $predicted_scientific_name
 * @property float|null $confidence
 * @property string|null $enriched_scientific_name
 * @property string|null $enriched_common_name
 * @property string|null $enriched_family
 * @property string|null $enriched_wikipedia_url
 * @property string|null $enriched_photo_url
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $observed_at
 * @property string $created_at
 * @property string $updated_at
 * @property bool $is_published
 * @property bool $is_synced
 * @property string $sync_status
 * @property int|null $last_sync_attempt_at
 * @property string|null $notes
 *
 * @property AppUser $user
 * @property PlantSpecies|null $plantSpecies
 * @property ObservationImage[] $observationImages
 * @property Publication|null $publication
 */
class Observation extends ActiveRecord
{
    public const SYNC_PENDING = 'PENDING';
    public const SYNC_SYNCED = 'SYNCED';
    public const SYNC_FAILED = 'FAILED';

    public static function tableName(): string
    {
        return '{{%observation}}';
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
            [['user_id'], 'required'],
            [['user_id', 'plant_species_id', 'captured_at', 'last_sync_attempt_at'], 'integer'],
            [['confidence', 'latitude', 'longitude'], 'number'],
            [['notes'], 'string'],
            [['is_published', 'is_synced'], 'boolean'],
            [['observed_at', 'created_at', 'updated_at'], 'safe'],
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
            ], 'string'],
            [['device_observation_id'], 'unique'],
            [['confidence'], 'compare', 'compareValue' => 0, 'operator' => '>='],
            [['confidence'], 'compare', 'compareValue' => 1, 'operator' => '<='],
            [['latitude'], 'compare', 'compareValue' => -90, 'operator' => '>='],
            [['latitude'], 'compare', 'compareValue' => 90, 'operator' => '<='],
            [['longitude'], 'compare', 'compareValue' => -180, 'operator' => '>='],
            [['longitude'], 'compare', 'compareValue' => 180, 'operator' => '<='],
            [['sync_status'], 'in', 'range' => [self::SYNC_PENDING, self::SYNC_SYNCED, self::SYNC_FAILED]],
            [['user_id'], 'exist', 'targetClass' => AppUser::class, 'targetAttribute' => ['user_id' => 'user_id']],
            [['plant_species_id'], 'exist', 'targetClass' => PlantSpecies::class, 'targetAttribute' => ['plant_species_id' => 'plant_species_id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'observation_id' => 'ID',
            'device_observation_id' => 'ID do Dispositivo',
            'user_id' => 'Utilizador',
            'plant_species_id' => 'Especie',
            'image_uri' => 'Imagem Principal',
            'captured_at' => 'Capturada em Epoch',
            'predicted_scientific_name' => 'Nome Cientifico Previsto',
            'confidence' => 'Confianca',
            'enriched_scientific_name' => 'Nome Cientifico Enriquecido',
            'enriched_common_name' => 'Nome Comum Enriquecido',
            'enriched_family' => 'Familia Enriquecida',
            'enriched_wikipedia_url' => 'Wikipedia',
            'enriched_photo_url' => 'Foto Enriquecida',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'observed_at' => 'Observada em',
            'is_published' => 'Publicada',
            'is_synced' => 'Sincronizada',
            'sync_status' => 'Estado de Sincronizacao',
            'last_sync_attempt_at' => 'Ultima Tentativa de Sync',
            'notes' => 'Notas',
            'created_at' => 'Criada em',
            'updated_at' => 'Atualizada em',
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(AppUser::class, ['user_id' => 'user_id']);
    }

    public function getPlantSpecies(): ActiveQuery
    {
        return $this->hasOne(PlantSpecies::class, ['plant_species_id' => 'plant_species_id']);
    }

    public function getObservationImages(): ActiveQuery
    {
        return $this->hasMany(ObservationImage::class, ['observation_id' => 'observation_id']);
    }

    public function getPublication(): ActiveQuery
    {
        return $this->hasOne(Publication::class, ['observation_id' => 'observation_id']);
    }

    public function getResolvedScientificName(): ?string
    {
        return $this->enriched_scientific_name ?: $this->predicted_scientific_name ?: $this->plantSpecies?->scientific_name;
    }

    public function getResolvedCommonName(): ?string
    {
        return $this->enriched_common_name ?: $this->plantSpecies?->common_name;
    }

    public function getResolvedFamily(): ?string
    {
        return $this->enriched_family ?: $this->plantSpecies?->family;
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
