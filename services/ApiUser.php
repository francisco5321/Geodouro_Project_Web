<?php

namespace app\services;

class ApiUser extends ApiDataObject
{
    public ?int $user_id = null;
    public ?string $username = null;
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $email = null;
    public ?string $displayName = null;

    public static function fromArray(array $data): self
    {
        return new self([
            'user_id' => self::first($data, ['user_id', 'userId', 'id']) !== null ? (int) self::first($data, ['user_id', 'userId', 'id']) : null,
            'username' => self::stringOrNull(self::first($data, ['username'])),
            'first_name' => self::stringOrNull(self::first($data, ['first_name', 'firstName'])),
            'last_name' => self::stringOrNull(self::first($data, ['last_name', 'lastName'])),
            'email' => self::stringOrNull(self::first($data, ['email'])),
            'displayName' => self::stringOrNull(self::first($data, ['displayName', 'display_name', 'fullName', 'name'])),
        ]);
    }

    public function getFullName(): string
    {
        $name = trim((string) ($this->displayName ?: trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''))));
        return $name !== '' ? $name : ($this->username ?: $this->email ?: 'Utilizador');
    }

    private static function stringOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }
}
