<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $publication_image_id
 * @property int $publication_id
 * @property string $image_path
 * @property string|null $thumbnail_path
 * @property string|null $mime_type
 * @property int|null $file_size_bytes
 * @property int|null $width_px
 * @property int|null $height_px
 * @property string $created_at
 *
 * @property Publication $publication
 */
class PublicationImage extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%publication_image}}';
    }

    public function rules(): array
    {
        return [
            [['publication_id', 'image_path'], 'required'],
            [['publication_id', 'file_size_bytes', 'width_px', 'height_px'], 'integer'],
            [['created_at'], 'safe'],
            [['image_path', 'thumbnail_path', 'mime_type'], 'string'],
            [['publication_id'], 'exist', 'targetClass' => Publication::class, 'targetAttribute' => ['publication_id' => 'publication_id']],
        ];
    }

    public function getPublication(): ActiveQuery
    {
        return $this->hasOne(Publication::class, ['publication_id' => 'publication_id']);
    }
}
