<?php

namespace app\models;

use RuntimeException;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public string $username = '';
    public string $password = '';
    public bool $rememberMe = true;

    private ?AppUser $_user = null;

    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            [['rememberMe'], 'boolean'],
            [['username', 'password'], 'string', 'max' => 255],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
            'rememberMe' => 'Manter sessão iniciada',
        ];
    }

    public function validatePassword(string $attribute, $params = null): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();
        if ($user === null || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Credenciais inválidas.');
        }
    }

    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $backendAuthRequired = (bool) (Yii::$app->params['backendAuthRequired'] ?? false);
        $backendAuthTimeout = (int) (Yii::$app->params['backendAuthTimeoutSeconds'] ?? 3);

        try {
            Yii::$app->backendAuthSession->syncLogin($this->username, $this->password, $backendAuthTimeout);
        } catch (RuntimeException $exception) {
            Yii::$app->backendAuthSession->clear();
            Yii::warning('Backend auth sync failed during web login: ' . $exception->getMessage(), __METHOD__);

            if ($backendAuthRequired) {
                $this->addError('password', 'Não foi possível ligar ao backend comum: ' . $exception->getMessage());
                return false;
            }

            Yii::$app->session->setFlash(
                'warning',
                'Sessão iniciada no portal. O backend comum não respondeu, por isso algumas ações sincronizadas podem ficar indisponíveis temporariamente.'
            );
        }

        return Yii::$app->user->login($this->getUser(), 0);
    }

    public function getUser(): ?AppUser
    {
        if ($this->_user === null) {
            $this->_user = AppUser::findByUsername($this->username);
        }

        return $this->_user;
    }
}
