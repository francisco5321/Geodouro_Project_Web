<?php

namespace app\models;

use yii\base\Model;

class ProfileForm extends Model
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $username = '';

    private AppUser $user;

    public function __construct(AppUser $user, $config = [])
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
            ['email', 'validateEmailIsUnique'],
            ['username', 'validateUsernameIsUnique'],
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

        $this->user->first_name = $this->first_name;
        $this->user->last_name = $this->last_name;
        $this->user->email = $this->email;
        $this->user->username = $this->username;

        return $this->user->save(false, ['first_name', 'last_name', 'email', 'username', 'updated_at']);
    }

    public function validateEmailIsUnique(string $attribute, $params = null): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $exists = AppUser::find()
            ->andWhere(['email' => $this->email])
            ->andWhere(['<>', 'user_id', $this->user->user_id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'Já existe uma conta com este email.');
        }
    }

    public function validateUsernameIsUnique(string $attribute, $params = null): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $exists = AppUser::find()
            ->andWhere(['username' => $this->username])
            ->andWhere(['<>', 'user_id', $this->user->user_id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'Este username já está em uso.');
        }
    }
}
