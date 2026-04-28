<?php

namespace app\models;

use Yii;
use yii\base\BaseObject;
use yii\web\IdentityInterface;

class ApiIdentity extends BaseObject implements IdentityInterface
{
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    public int $user_id;
    public string $username = '';
    public string $email = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $role = self::ROLE_USER;
    public ?string $displayName = null;

    public static function fromArray(array $data): self
    {
        return new self([
            'user_id' => (int) ($data['userId'] ?? $data['user_id'] ?? 0),
            'username' => (string) ($data['username'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'first_name' => (string) ($data['firstName'] ?? $data['first_name'] ?? ''),
            'last_name' => (string) ($data['lastName'] ?? $data['last_name'] ?? ''),
            'displayName' => $data['displayName'] ?? $data['display_name'] ?? null,
            'role' => (string) ($data['role'] ?? self::ROLE_USER),
        ]);
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        $user = Yii::$app->backendAuthSession->getCurrentUser();
        if (!is_array($user) || (int) ($user['userId'] ?? 0) !== (int) $id) {
            return null;
        }

        return self::fromArray($user);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public function getId(): int
    {
        return $this->user_id;
    }

    public function getAuthKey(): ?string
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }

    public function getFullName(): string
    {
        $name = trim((string) ($this->displayName ?: trim($this->first_name . ' ' . $this->last_name)));
        return $name !== '' ? $name : ($this->username ?: $this->email);
    }

    public function getRoleName(): string
    {
        return $this->role ?: self::ROLE_USER;
    }

    public function getRoleLabel(): string
    {
        return $this->isAdmin() ? 'Administrador' : 'Utilizador';
    }

    public function isAdmin(): bool
    {
        return $this->getRoleName() === self::ROLE_ADMIN;
    }
}
