<?php

namespace app\services;

use Yii;
use yii\base\Component;

class AccountApiService extends Component
{
    public function login(string $identifier, string $password): array
    {
        return Yii::$app->backendApi->postJson('/api/auth/login', [
            'identifier' => trim($identifier),
            'password' => $password,
        ]);
    }

    public function signup(array $payload): array
    {
        return Yii::$app->backendApi->postJson('/api/auth/signup', $payload);
    }

    public function updateProfile(array $payload): array
    {
        return Yii::$app->backendApi->patchJson('/api/auth/me', $payload, $this->headers());
    }

    public function changePassword(string $currentPassword, string $newPassword): array
    {
        return Yii::$app->backendApi->patchJson('/api/auth/me/password', [
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword,
        ], $this->headers());
    }

    public function listUsers(): array
    {
        return Yii::$app->backendApi->getJson('/api/auth/users', $this->headers());
    }

    public function updateUserRole(int $userId, string $role): array
    {
        return Yii::$app->backendApi->patchJson('/api/auth/users/' . $userId . '/role', [
            'role' => $role,
        ], $this->headers());
    }

    private function headers(): array
    {
        return Yii::$app->backendAuthSession->getAuthorizationHeaders();
    }
}
