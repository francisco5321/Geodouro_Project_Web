<?php

namespace app\services;

class ApiObservationImage extends ApiDataObject
{
    public ?string $image_path = null;
    public ?string $thumbnail_path = null;

    public static function fromArray(array $data): self
    {
        return new self([
            'image_path' => self::stringOrNull(self::first($data, ['image_path', 'imagePath', 'path', 'url'])),
            'thumbnail_path' => self::stringOrNull(self::first($data, ['thumbnail_path', 'thumbnailPath', 'thumbnail', 'thumb'])),
        ]);
    }

    private static function stringOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }
}
