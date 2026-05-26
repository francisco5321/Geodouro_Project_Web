<?php

namespace app\models;

use yii\base\Model;

class SpeciesForm extends Model
{
    public ?int $plant_species_id = null;
    public ?string $scientific_name = null;
    public ?string $common_name = null;
    public ?string $family = null;
    public ?string $genus = null;
    public ?string $species = null;
    public ?int $image_count = null;
    public ?string $description = null;

    public function rules(): array
    {
        return [
            [['scientific_name', 'family', 'genus', 'species'], 'required'],
            [['description'], 'string'],
            [['image_count'], 'integer', 'min' => 0],
            [['scientific_name', 'common_name', 'family', 'genus', 'species'], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'plant_species_id' => 'ID',
            'scientific_name' => 'Nome Cientifico',
            'common_name' => 'Nome Comum',
            'family' => 'Familia',
            'genus' => 'Genero',
            'species' => 'Especie',
            'image_count' => 'Numero de Imagens',
            'description' => 'Descricao',
        ];
    }
}
