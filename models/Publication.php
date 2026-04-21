<?php

namespace app\models;

use Yii;
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
 * @property string $status
 * @property string $published_at
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Observation $observation
 * @property AppUser $user
 * @property PlantSpecies|null $plantSpecies
 * @property PublicationImage[] $publicationImages
 * @property SavedVisitTarget[] $savedVisitTargets
 */
class Publication extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

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
                'updatedAtAttribute' => 'updated_at',
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
            [['published_at', 'created_at', 'updated_at'], 'safe'],
            [['title', 'status'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
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
            'observation_id' => 'Observação',
            'user_id' => 'Autor',
            'plant_species_id' => 'Espécie',
            'title' => 'Titulo',
            'description' => 'Descrição',
            'status' => 'Estado editorial',
            'published_at' => 'Publicada em',
            'created_at' => 'Criada em',
            'updated_at' => 'Atualizada em',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Rascunho',
            self::STATUS_PUBLISHED => 'Publicada',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function canBeManagedBy(?AppUser $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->isAdmin() || (int) $user->user_id === (int) $this->user_id;
    }

    public function isSavedForUser(?AppUser $user): bool
    {
        if ($user === null) {
            return false;
        }

        return SavedVisitTarget::find()
            ->where([
                'user_id' => $user->user_id,
                'publication_id' => $this->publication_id,
            ])
            ->exists();
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

    public function getSavedVisitTargets(): ActiveQuery
    {
        return $this->hasMany(SavedVisitTarget::class, ['publication_id' => 'publication_id']);
    }

    public function getImageGalleryPaths(): array
    {
        $paths = [];

        foreach ($this->publicationImages as $image) {
            $candidate = trim((string) ($image->thumbnail_path ?: $image->image_path));
            if ($candidate !== '') {
                $paths[] = $candidate;
            }
        }

        return array_values(array_unique($paths));
    }

    public function getCoverImagePath(): ?string
    {
        return $this->getImageGalleryPaths()[0] ?? null;
    }
}
