<?php

namespace app\services;

class ApiObservation extends ApiDataObject
{
    public ?int $observation_id = null;
    public ?string $device_observation_id = null;
    public ?int $plant_species_id = null;
    public bool $requires_manual_identification = false;
    public ?string $image_uri = null;
    public ?string $predicted_scientific_name = null;
    public ?float $confidence = null;
    public ?string $enriched_scientific_name = null;
    public ?string $enriched_common_name = null;
    public ?string $enriched_family = null;
    public ?string $enriched_wikipedia_url = null;
    public ?string $enriched_photo_url = null;
    public ?int $user_id = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?string $observed_at = null;
    public ?string $notes = null;
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
            'user_id' => self::first($data, ['user_id', 'userId']) !== null ? (int) self::first($data, ['user_id', 'userId']) : null,
            'plant_species_id' => self::first($data, ['plant_species_id', 'plantSpeciesId', 'speciesId']) !== null ? (int) self::first($data, ['plant_species_id', 'plantSpeciesId', 'speciesId']) : null,
            'requires_manual_identification' => (bool) self::first($data, ['requires_manual_identification', 'requiresManualIdentification'], false),
            'image_uri' => self::stringOrNull(self::first($data, ['image_uri', 'imageUri', 'image'])),
            'predicted_scientific_name' => self::stringOrNull(self::first($data, ['predicted_scientific_name', 'predictedScientificName'])),
            'confidence' => self::first($data, ['confidence']) !== null ? (float) self::first($data, ['confidence']) : null,
            'enriched_scientific_name' => self::stringOrNull(self::first($data, ['enriched_scientific_name', 'enrichedScientificName'])),
            'enriched_common_name' => self::stringOrNull(self::first($data, ['enriched_common_name', 'enrichedCommonName'])),
            'enriched_family' => self::stringOrNull(self::first($data, ['enriched_family', 'enrichedFamily'])),
            'enriched_wikipedia_url' => self::stringOrNull(self::first($data, ['enriched_wikipedia_url', 'enrichedWikipediaUrl', 'wikipediaUrl'])),
            'enriched_photo_url' => self::stringOrNull(self::first($data, ['enriched_photo_url', 'enrichedPhotoUrl', 'photoUrl'])),
            'latitude' => self::first($data, ['latitude']) !== null ? (float) self::first($data, ['latitude']) : null,
            'longitude' => self::first($data, ['longitude']) !== null ? (float) self::first($data, ['longitude']) : null,
            'observed_at' => self::stringOrNull(self::first($data, ['observed_at', 'observedAt'])),
            'notes' => self::stringOrNull(self::first($data, ['notes'])),
            'is_published' => (bool) self::first($data, ['is_published', 'isPublished'], false),
            'sync_status' => (string) self::first($data, ['sync_status', 'syncStatus'], 'PENDING'),
            'publication' => is_array($publication) ? ApiPublication::fromArray($publication) : null,
            'observationImages' => self::imageObjects($images),
        ]);
    }

    public function getResolvedScientificName(): ?string
    {
        return $this->enriched_scientific_name ?: $this->predicted_scientific_name;
    }

    public function getResolvedCommonName(): ?string
    {
        return $this->enriched_common_name;
    }

    public function getResolvedFamily(): ?string
    {
        return $this->enriched_family;
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function needsManualReview(): bool
    {
        return $this->requires_manual_identification;
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

    /**
     * @return ApiObservationImage[]
     */
    private static function imageObjects(mixed $images): array
    {
        if (!is_array($images)) {
            return [];
        }

        if (array_is_list($images) && isset($images[0]) && is_string($images[0])) {
            return array_map(
                static fn (mixed $path): ApiObservationImage => ApiObservationImage::fromArray(['imagePath' => (string) $path]),
                array_filter($images, static fn (mixed $path): bool => trim((string) $path) !== '')
            );
        }

        return array_map(
            static fn (array $image): ApiObservationImage => ApiObservationImage::fromArray($image),
            array_filter($images, 'is_array')
        );
    }
}
