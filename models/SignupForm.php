<?php

namespace app\models;

use Yii;
use yii\base\Model;

class SignupForm extends Model
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $username = '';
    public string $password = '';
    public string $passwordRepeat = '';

    public function rules(): array
    {
        return [
            [['first_name', 'last_name', 'email', 'username', 'password', 'passwordRepeat'], 'required'],
            [['first_name', 'last_name', 'email', 'username'], 'filter', 'filter' => 'trim'],
            [['first_name', 'last_name'], 'string', 'max' => 120],
            [['email', 'username'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['password', 'passwordRepeat'], 'string', 'min' => 8, 'max' => 255],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password', 'message' => 'As passwords não coincidem.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'first_name' => 'Primeiro nome',
            'last_name' => 'Ultimo nome',
            'email' => 'Email',
            'username' => 'Username',
            'password' => 'Password',
            'passwordRepeat' => 'Confirmar password',
        ];
    }

    public function signup(): ?ApiIdentity
    {
        if (!$this->validate()) {
            return null;
        }

        try {
            $response = Yii::$app->accountApi->signup([
                'firstName' => $this->first_name,
                'lastName' => $this->last_name,
                'email' => $this->email,
                'username' => $this->username,
                'password' => $this->password,
            ]);

            return Yii::$app->backendAuthSession->establishFromResponse($response);
        } catch (\RuntimeException $exception) {
            $this->addError('email', $exception->getMessage());
            return null;
        }
    }
}
