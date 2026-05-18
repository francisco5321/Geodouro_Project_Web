<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public string $username = '';
    public string $password = '';
    public bool $rememberMe = true;

    private ?ApiIdentity $_user = null;

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
            'username' => 'Username ou Email',
            'password' => 'Password',
            'rememberMe' => 'Manter sessão iniciada',
        ];
    }

    public function validatePassword(string $attribute, $params = null): void
    {
        if ($this->hasErrors()) {
            return;
        }

        try {
            $response = Yii::$app->accountApi->login($this->username, $this->password);
            $this->_user = Yii::$app->backendAuthSession->establishFromResponse($response);
        } catch (\RuntimeException $exception) {
            Yii::$app->backendAuthSession->clear();
            $this->addError($attribute, 'Credenciais invalidas.');
        }
    }

    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        return Yii::$app->user->login($this->getUser(), 0);
    }

    public function getUser(): ?ApiIdentity
    {
        return $this->_user;
    }
}
