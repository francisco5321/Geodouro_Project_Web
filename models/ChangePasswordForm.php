<?php

namespace app\models;

use RuntimeException;
use Yii;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordRepeat = '';

    private AppUser $user;

    public function __construct(AppUser $user, $config = [])
    {
        $this->user = $user;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['currentPassword', 'newPassword', 'newPasswordRepeat'], 'required'],
            [['currentPassword', 'newPassword', 'newPasswordRepeat'], 'string', 'min' => 8, 'max' => 255],
            ['currentPassword', 'validateCurrentPassword'],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword', 'message' => 'As passwords novas não coincidem.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'currentPassword' => 'Password atual',
            'newPassword' => 'Nova password',
            'newPasswordRepeat' => 'Confirmar nova password',
        ];
    }

    public function validateCurrentPassword(string $attribute, $params = null): void
    {
        if ($this->hasErrors()) {
            return;
        }

        if (!$this->user->validatePassword($this->currentPassword)) {
            $this->addError($attribute, 'A password atual não está correta.');
        }
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->user->setPassword($this->newPassword);
        if (!$this->user->save(false, ['password_hash', 'updated_at'])) {
            return false;
        }

        try {
            Yii::$app->backendAuthSession->refreshForUser($this->user, $this->newPassword);
        } catch (RuntimeException $exception) {
            $this->addError('newPassword', 'A password foi atualizada localmente, mas a sessão com o backend não foi renovada: ' . $exception->getMessage());
            return false;
        }

        return true;
    }
}
