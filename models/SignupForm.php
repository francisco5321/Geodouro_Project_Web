<?php

namespace app\models;

use RuntimeException;
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
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password', 'message' => 'As passwords nao coincidem.'],
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
            'password' => 'Password',
            'passwordRepeat' => 'Confirmar password',
        ];
    }

    public function validateEmailIsUnique(string $attribute, $params = null): void
    {
        if ($this->hasErrors()) {
            return;
        }

        if (AppUser::find()->andWhere(['email' => $this->email])->exists()) {
            $this->addError($attribute, 'Ja existe uma conta com este email.');
        }
    }

    public function validateUsernameIsUnique(string $attribute, $params = null): void
    {
        if ($this->hasErrors()) {
            return;
        }

        if (AppUser::find()->andWhere(['username' => $this->username])->exists()) {
            $this->addError($attribute, 'Este username ja esta em uso.');
        }
    }

    public function signup(): ?AppUser
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new AppUser();
        $user->is_authenticated = true;
        $user->guest_label = $this->generateGuestLabel();
        $user->first_name = $this->first_name;
        $user->last_name = $this->last_name;
        $user->email = $this->email;
        $user->username = $this->username;
        if ($user->hasAttribute('role')) {
            $user->role = AppUser::ROLE_USER;
        }
        $user->setPassword($this->password);
        $user->generateAuthKey();

        if (!$user->save()) {
            return null;
        }

        try {
            Yii::$app->backendAuthSession->refreshForUser($user, $this->password);
        } catch (RuntimeException $exception) {
            $this->addError('password', 'A conta foi criada, mas nao foi possivel sincronizar a sessao com o backend: ' . $exception->getMessage());
            return null;
        }

        return $user;
    }

    private function generateGuestLabel(): string
    {
        do {
            $candidate = 'web-' . Yii::$app->security->generateRandomString(12);
        } while (AppUser::find()->andWhere(['guest_label' => $candidate])->exists());

        return $candidate;
    }
}
