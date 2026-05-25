<?php

namespace app\services;

class ApiPublication extends ApiDataObject
{
    public ?int $publication_id = null;
    public ?int $observation_id = null;
    public ?string $device_observation_id = null;
    public ?int $user_id = null;
    public ?int $plant_species_id = null;
    public ?string $title = null;
    public ?string $description = null;
    public string $status = 'published';
    public ?string $published_at = null;
    public ?ApiObservation $observation = null;
    public ?ApiPlantSpecies $plantSpecies = null;
    public ?ApiUser $user = null;
    /** @var ApiObservationImage[] */
    public array $publicationImages = [];

    public static function fromArray(array $data): self
    {
        $user = self::first($data, ['user']);
        $observation = self::first($data, ['observation']);
        $plantSpecies = self::first($data, ['plantSpecies', 'plant_species']);
        $imagePath = self::stringOrNull(self::first($data, ['imagePath', 'image_path', 'coverImagePath']));

        return new self([
            'publication_id' => self::first($data, ['publication_id', 'publicationId', 'id']) !== null ? (int) self::first($data, ['publication_id', 'publicationId', 'id']) : null,
            'observation_id' => self::first($data, ['observation_id', 'observationId']) !== null ? (int) self::first($data, ['observation_id', 'observationId']) : null,
            'device_observation_id' => self::stringOrNull(self::first($data, ['device_observation_id', 'deviceObservationId'])),
            'user_id' => self::first($data, ['user_id', 'userId']) !== null ? (int) self::first($data, ['user_id', 'userId']) : null,
            'plant_species_id' => self::first($data, ['plant_species_id', 'plantSpeciesId']) !== null ? (int) self::first($data, ['plant_species_id', 'plantSpeciesId']) : null,
            'title' => self::stringOrNull(self::first($data, ['title'])) ?: self::stringOrNull(self::first($data, ['commonName', 'common_name', 'scientificName', 'scientific_name'])),
            'description' => self::stringOrNull(self::first($data, ['description'])),
            'status' => (string) self::first($data, ['status'], 'published'),
            'published_at' => self::formatInstant(self::first($data, ['published_at', 'publishedAt'])),
            'user' => is_array($user) ? ApiUser::fromArray($user) : ApiUser::fromArray([
                'displayName' => self::first($data, ['userDisplayName', 'user_display_name']),
            ]),
            'observation' => is_array($observation) ? ApiObservation::fromArray($observation) : ApiObservation::fromArray([
                'observationId' => self::first($data, ['observationId', 'observation_id']),
                'deviceObservationId' => self::first($data, ['deviceObservationId', 'device_observation_id']),
                'scientificName' => self::first($data, ['scientificName', 'scientific_name']),
                'commonName' => self::first($data, ['commonName', 'common_name']),
                'image_uri' => $imagePath,
                'latitude' => self::first($data, ['latitude']),
                'longitude' => self::first($data, ['longitude']),
                'isPublished' => true,
                'syncStatus' => 'SYNCED',
            ]),
            'plantSpecies' => is_array($plantSpecies) ? ApiPlantSpecies::fromArray($plantSpecies) : null,
            'publicationImages' => $imagePath !== null ? [ApiObservationImage::fromArray(['imagePath' => $imagePath])] : [],
        ]);
    }

    public function getStatusLabel(): string
    {
        return $this->isPublished() ? 'Publicada' : 'Rascunho';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function canBeManagedBy($user): bool
    {
        if ($user === null) {
            return false;
        }

        return ($user->isAdmin() ?? false) || (int) $user->user_id === (int) $this->user_id;
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

    private static function stringOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }

    private static function formatInstant(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp !== false ? gmdate('Y-m-d H:i:s', $timestamp) : $value;
    }
}
