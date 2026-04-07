<?php

namespace app\models;

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
            'rememberMe' => 'Manter sessao iniciada',
        ];
    }

    public function validatePassword(string $attribute, $params = null): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();
        if ($user === null || !$user->validatePassword($this->password)) {
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

    public function getUser(): ?AppUser
    {
        if ($this->_user === null) {
            $this->_user = AppUser::findByUsername($this->username);
        }

        return $this->_user;
    }
}
