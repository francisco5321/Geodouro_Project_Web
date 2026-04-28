<?php

namespace app\services;

class ApiPlantSpecies extends ApiDataObject
{
    public ?int $plant_species_id = null;
    public ?string $species_id = null;
    public ?string $scientific_name = null;
    public ?string $common_name = null;
    public ?string $family = null;
    public ?string $genus = null;
    public ?string $species = null;
    public int $image_count = 0;
    public ?string $description = null;

    public static function fromArray(array $data): self
    {
        return new self([
            'plant_species_id' => self::first($data, ['plant_species_id', 'plantSpeciesId', 'speciesId', 'id']) !== null ? (int) self::first($data, ['plant_species_id', 'plantSpeciesId', 'speciesId', 'id']) : null,
            'species_id' => self::stringOrNull(self::first($data, ['species_id', 'speciesId', 'slug'])),
            'scientific_name' => self::stringOrNull(self::first($data, ['scientific_name', 'scientificName'])),
            'common_name' => self::stringOrNull(self::first($data, ['common_name', 'commonName'])),
            'family' => self::stringOrNull(self::first($data, ['family'])),
            'genus' => self::stringOrNull(self::first($data, ['genus'])),
            'species' => self::stringOrNull(self::first($data, ['species', 'specificEpithet'])),
            'image_count' => (int) self::first($data, ['image_count', 'imageCount'], 0),
            'description' => self::stringOrNull(self::first($data, ['description'])),
        ]);
    }

    public function getDisplayName(): string
    {
        return $this->common_name ?: (string) $this->scientific_name;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }
}
