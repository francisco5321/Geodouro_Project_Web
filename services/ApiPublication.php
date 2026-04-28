<?php

namespace app\services;

class ApiPublication extends ApiDataObject
{
    public ?int $publication_id = null;
    public ?ApiUser $user = null;

    public static function fromArray(array $data): self
    {
        $user = self::first($data, ['user']);

        return new self([
            'publication_id' => self::first($data, ['publication_id', 'publicationId', 'id']) !== null ? (int) self::first($data, ['publication_id', 'publicationId', 'id']) : null,
            'user' => is_array($user) ? ApiUser::fromArray($user) : null,
        ]);
    }
}
