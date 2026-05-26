<?php

namespace app\models;

use yii\base\Model;

class PublicationForm extends Model
{
    public ?int $publication_id = null;
    public ?int $observation_id = null;
    public ?int $user_id = null;
    public ?int $plant_species_id = null;
    public ?string $title = null;
    public ?string $description = null;
    public string $status = Publication::STATUS_PUBLISHED;
    public ?string $published_at = null;
    public bool $isNewRecord = true;

    public function rules(): array
    {
        return [
            [['observation_id', 'user_id'], 'required'],
            [['observation_id', 'user_id', 'plant_species_id'], 'integer'],
            [['description'], 'string'],
            [['published_at'], 'safe'],
            [['title', 'status'], 'string'],
            [['status'], 'in', 'range' => [Publication::STATUS_DRAFT, Publication::STATUS_PUBLISHED]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'publication_id' => 'ID',
            'observation_id' => 'Observação',
            'user_id' => 'Autor',
            'plant_species_id' => 'Espécie',
            'title' => 'Título',
            'description' => 'Descrição',
            'status' => 'Estado editorial',
            'published_at' => 'Publicada em',
        ];
    }
}
