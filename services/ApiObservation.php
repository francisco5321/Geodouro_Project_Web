<?php

namespace app\services;

class ApiObservation extends ApiDataObject
{
    public ?int $observation_id = null;
    public ?string $device_observation_id = null;
    public ?int $plant_species_id = null;
    public ?string $image_uri = null;
    public ?string $predicted_scientific_name = null;
    public ?float $confidence = null;
    public ?string $enriched_scientific_name = null;
    public ?string $enriched_common_name = null;
    public ?string $enriched_family = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?string $observed_at = null;
    public bool $is_published = false;
    public string $sync_status = 'PENDING';
    public ?ApiPublication $publication = null;
    /** @var ApiObservationImage[] */
    public array $observationImages = [];

    public static function fromArray(array $data): self
    {
        $publication = self::first($data, ['publication']);
        $images = self::first($data, ['observationImages', 'observation_images', 'images'], []);

        return new self([
            'observation_id' => self::first($data, ['observation_id', 'observationId', 'id']) !== null ? (int) self::first($data, ['observation_id', 'observationId', 'id']) : null,
            'device_observation_id' => self::stringOrNull(self::first($data, ['device_observation_id', 'deviceObservationId'])),
            'plant_species_id' => self::first($data, ['plant_species_id', 'plantSpeciesId', 'speciesId']) !== null ? (int) self::first($data, ['plant_species_id', 'plantSpeciesId', 'speciesId']) : null,
            'image_uri' => self::stringOrNull(self::first($data, ['image_uri', 'imageUri', 'image'])),
            'predicted_scientific_name' => self::stringOrNull(self::first($data, ['predicted_scientific_name', 'predictedScientificName'])),
            'confidence' => self::first($data, ['confidence']) !== null ? (float) self::first($data, ['confidence']) : null,
            'enriched_scientific_name' => self::stringOrNull(self::first($data, ['enriched_scientific_name', 'enrichedScientificName'])),
            'enriched_common_name' => self::stringOrNull(self::first($data, ['enriched_common_name', 'enrichedCommonName'])),
            'enriched_family' => self::stringOrNull(self::first($data, ['enriched_family', 'enrichedFamily'])),
            'latitude' => self::first($data, ['latitude']) !== null ? (float) self::first($data, ['latitude']) : null,
            'longitude' => self::first($data, ['longitude']) !== null ? (float) self::first($data, ['longitude']) : null,
            'observed_at' => self::stringOrNull(self::first($data, ['observed_at', 'observedAt'])),
            'is_published' => (bool) self::first($data, ['is_published', 'isPublished'], false),
            'sync_status' => (string) self::first($data, ['sync_status', 'syncStatus'], 'PENDING'),
            'publication' => is_array($publication) ? ApiPublication::fromArray($publication) : null,
            'observationImages' => is_array($images) ? array_map(
                static fn (array $image): ApiObservationImage => ApiObservationImage::fromArray($image),
                array_filter($images, 'is_array')
            ) : [],
        ]);
    }

    public function getResolvedCommonName(): ?string
    {
        return $this->enriched_common_name;
    }

    public function getImageGalleryPaths(): array
    {
        $paths = [];
        foreach ($this->observationImages as $image) {
            $candidate = trim((string) ($image->thumbnail_path ?: $image->image_path));
            if ($candidate !== '') {
                $paths[] = $candidate;
            }
        }

        $fallback = trim((string) $this->image_uri);
        if ($paths === [] && $fallback !== '' && !str_starts_with($fallback, 'file://')) {
            $paths[] = $fallback;
        }

        return array_values(array_unique($paths));
    }

    private static function stringOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }
}
