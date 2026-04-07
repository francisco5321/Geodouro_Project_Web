<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $observation_image_id
 * @property int $observation_id
 * @property string $image_path
 * @property string|null $thumbnail_path
 * @property string|null $mime_type
 * @property int|null $file_size_bytes
 * @property int|null $width_px
 * @property int|null $height_px
 * @property string $created_at
 *
 * @property Observation $observation
 */
class ObservationImage extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%observation_image}}';
    }

    public function rules(): array
    {
        return [
            [['observation_id', 'image_path'], 'required'],
            [['observation_id', 'file_size_bytes', 'width_px', 'height_px'], 'integer'],
            [['created_at'], 'safe'],
            [['image_path', 'thumbnail_path', 'mime_type'], 'string'],
            [['observation_id'], 'exist', 'targetClass' => Observation::class, 'targetAttribute' => ['observation_id' => 'observation_id']],
        ];
    }

    public function getObservation(): ActiveQuery
    {
        return $this->hasOne(Observation::class, ['observation_id' => 'observation_id']);
    }
}
