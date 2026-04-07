<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $publication_id
 * @property int $observation_id
 * @property int $user_id
 * @property int|null $plant_species_id
 * @property string|null $title
 * @property string|null $description
 * @property string $published_at
 * @property string $created_at
 *
 * @property Observation $observation
 * @property AppUser $user
 * @property PlantSpecies|null $plantSpecies
 * @property PublicationImage[] $publicationImages
 */
class Publication extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%publication}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['observation_id', 'user_id'], 'required'],
            [['observation_id', 'user_id', 'plant_species_id'], 'integer'],
            [['description'], 'string'],
            [['published_at', 'created_at'], 'safe'],
            [['title'], 'string'],
            [['observation_id'], 'unique'],
            [['observation_id'], 'exist', 'targetClass' => Observation::class, 'targetAttribute' => ['observation_id' => 'observation_id']],
            [['user_id'], 'exist', 'targetClass' => AppUser::class, 'targetAttribute' => ['user_id' => 'user_id']],
            [['plant_species_id'], 'exist', 'targetClass' => PlantSpecies::class, 'targetAttribute' => ['plant_species_id' => 'plant_species_id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'publication_id' => 'ID',
            'observation_id' => 'Observacao',
            'user_id' => 'Autor',
            'plant_species_id' => 'Especie',
            'title' => 'Titulo',
            'description' => 'Descricao',
            'published_at' => 'Publicada em',
            'created_at' => 'Criada em',
        ];
    }

    public function getObservation(): ActiveQuery
    {
        return $this->hasOne(Observation::class, ['observation_id' => 'observation_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(AppUser::class, ['user_id' => 'user_id']);
    }

    public function getPlantSpecies(): ActiveQuery
    {
        return $this->hasOne(PlantSpecies::class, ['plant_species_id' => 'plant_species_id']);
    }

    public function getPublicationImages(): ActiveQuery
    {
        return $this->hasMany(PublicationImage::class, ['publication_id' => 'publication_id']);
    }
}
