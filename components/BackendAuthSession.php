<?php

namespace app\components;

use app\models\ApiIdentity;
use app\models\AppUser;
use RuntimeException;
use Yii;
use yii\base\Component;

class BackendAuthSession extends Component
{
    private const TOKEN_KEY = '__backend_auth_token';
    private const USER_KEY = '__backend_auth_user';

    public function establishForUser(AppUser $user): void
    {
        $secret = (string) (Yii::$app->params['backendTokenSecret'] ?? '');
        if ($secret === '') {
            throw new RuntimeException('Backend token secret is not configured.');
        }

        $userId = (string) $user->getId();
        $authToken = $userId . '.' . hash_hmac('sha256', $userId, $secret);

        Yii::$app->session->set(self::TOKEN_KEY, $authToken);
        Yii::$app->session->set(self::USER_KEY, [
            'userId' => $user->getId(),
            'username' => $user->username,
            'email' => $user->email,
            'displayName' => $user->getFullName(),
        ]);
    }

    public function syncLogin(string $identifier, string $password, ?int $timeoutSeconds = null): void
    {
        $response = Yii::$app->backendApi->postJson('/api/auth/login', [
            'identifier' => trim($identifier),
            'password' => $password,
        ], [], $timeoutSeconds);

        $authToken = trim((string) ($response['authToken'] ?? ''));
        if ($authToken === '') {
            throw new RuntimeException('Backend login did not return an auth token.');
        }

        Yii::$app->session->set(self::TOKEN_KEY, $authToken);
        Yii::$app->session->set(self::USER_KEY, [
            'userId' => $response['userId'] ?? null,
            'username' => $response['username'] ?? null,
            'email' => $response['email'] ?? null,
            'firstName' => $response['firstName'] ?? null,
            'lastName' => $response['lastName'] ?? null,
            'displayName' => $response['displayName'] ?? null,
            'role' => $response['role'] ?? ApiIdentity::ROLE_USER,
        ]);
    }

    public function establishFromResponse(array $response): ApiIdentity
    {
        $authToken = trim((string) ($response['authToken'] ?? ''));
        if ($authToken === '') {
            throw new RuntimeException('Backend response did not return an auth token.');
        }

        $user = [
            'userId' => $response['userId'] ?? null,
            'username' => $response['username'] ?? null,
            'email' => $response['email'] ?? null,
            'firstName' => $response['firstName'] ?? null,
            'lastName' => $response['lastName'] ?? null,
            'displayName' => $response['displayName'] ?? null,
            'role' => $response['role'] ?? ApiIdentity::ROLE_USER,
        ];

        Yii::$app->session->set(self::TOKEN_KEY, $authToken);
        Yii::$app->session->set(self::USER_KEY, $user);

        return ApiIdentity::fromArray($user);
    }

    public function replaceCurrentUser(array $response): ApiIdentity
    {
        $user = [
            'userId' => $response['userId'] ?? null,
            'username' => $response['username'] ?? null,
            'email' => $response['email'] ?? null,
            'firstName' => $response['firstName'] ?? null,
            'lastName' => $response['lastName'] ?? null,
            'displayName' => $response['displayName'] ?? null,
            'role' => $response['role'] ?? ApiIdentity::ROLE_USER,
        ];

        Yii::$app->session->set(self::USER_KEY, $user);
        return ApiIdentity::fromArray($user);
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
