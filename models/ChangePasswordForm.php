<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordRepeat = '';

    private ApiIdentity $user;

    public function __construct(ApiIdentity $user, $config = [])
    {
        $this->user = $user;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['currentPassword', 'newPassword', 'newPasswordRepeat'], 'required'],
            [['currentPassword', 'newPassword', 'newPasswordRepeat'], 'string', 'min' => 8, 'max' => 255],
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

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        try {
            $response = Yii::$app->accountApi->changePassword($this->currentPassword, $this->newPassword);
            Yii::$app->backendAuthSession->establishFromResponse($response);
            return true;
        } catch (\RuntimeException $exception) {
            $this->addError('currentPassword', $exception->getMessage());
            return false;
        }
    }
}
