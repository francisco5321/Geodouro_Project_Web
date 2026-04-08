<?php

namespace app\components;

use app\models\AppUser;
use RuntimeException;
use Yii;
use yii\base\Component;

class BackendAuthSession extends Component
{
    private const TOKEN_KEY = '__backend_auth_token';
    private const USER_KEY = '__backend_auth_user';

    public function syncLogin(string $identifier, string $password): void
    {
        $response = Yii::$app->backendApi->postJson('/api/auth/login', [
            'identifier' => trim($identifier),
            'password' => $password,
        ]);

        $authToken = trim((string) ($response['authToken'] ?? ''));
        if ($authToken === '') {
            throw new RuntimeException('Backend login did not return an auth token.');
        }

        Yii::$app->session->set(self::TOKEN_KEY, $authToken);
        Yii::$app->session->set(self::USER_KEY, [
            'userId' => $response['userId'] ?? null,
            'username' => $response['username'] ?? null,
            'email' => $response['email'] ?? null,
            'displayName' => $response['displayName'] ?? null,
        ]);
    }

    public function refreshForUser(AppUser $user, string $password): void
    {
        $identifier = trim((string) ($user->username ?: $user->email));
        if ($identifier === '') {
            throw new RuntimeException('The authenticated user has no username or email to authenticate with the backend.');
        }

        $this->syncLogin($identifier, $password);
    }

    public function clear(): void
    {
        Yii::$app->session->remove(self::TOKEN_KEY);
        Yii::$app->session->remove(self::USER_KEY);
    }

    public function getAccessToken(): ?string
    {
        $token = trim((string) Yii::$app->session->get(self::TOKEN_KEY, ''));
        return $token !== '' ? $token : null;
    }

    public function getAuthorizationHeaders(): array
    {
        $token = $this->getAccessToken();
        return $token === null ? [] : ['Authorization' => 'Bearer ' . $token];
    }

    public function getCurrentUser(): ?array
    {
        $user = Yii::$app->session->get(self::USER_KEY);
        return is_array($user) ? $user : null;
    }
}
