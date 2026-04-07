<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property string $species_query
 * @property int|null $taxon_id
 * @property string $scientific_name
 * @property string|null $common_name
 * @property string|null $family
 * @property string|null $wikipedia_url
 * @property string|null $photo_url
 * @property int $updated_at
 * @property string $created_at
 */
class TaxonCache extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%taxon_cache}}';
    }

    public static function primaryKey(): array
    {
        return ['species_query'];
    }

    public function rules(): array
    {
        return [
            [['species_query', 'scientific_name', 'updated_at'], 'required'],
            [['taxon_id', 'updated_at'], 'integer'],
            [['created_at'], 'safe'],
            [['species_query', 'scientific_name', 'common_name', 'family', 'wikipedia_url', 'photo_url'], 'string'],
            [['species_query'], 'unique'],
        ];
    }
}
