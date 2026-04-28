<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ProfileForm extends Model
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $username = '';

    private ApiIdentity $user;

    public function __construct(ApiIdentity $user, $config = [])
    {
        $this->user = $user;
        $this->first_name = (string) $user->first_name;
        $this->last_name = (string) $user->last_name;
        $this->email = (string) $user->email;
        $this->username = (string) $user->username;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['first_name', 'last_name', 'email', 'username'], 'required'],
            [['first_name', 'last_name', 'email', 'username'], 'filter', 'filter' => 'trim'],
            [['first_name', 'last_name'], 'string', 'max' => 120],
            [['email', 'username'], 'string', 'max' => 255],
            [['email'], 'email'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'first_name' => 'Primeiro nome',
            'last_name' => 'Ultimo nome',
            'email' => 'Email',
            'username' => 'Username',
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        try {
            $response = Yii::$app->accountApi->updateProfile([
                'firstName' => $this->first_name,
                'lastName' => $this->last_name,
                'email' => $this->email,
                'username' => $this->username,
            ]);
            Yii::$app->backendAuthSession->replaceCurrentUser($response);
            return true;
        } catch (\RuntimeException $exception) {
            $this->addError('email', $exception->getMessage());
            return false;
        }
    }
}
