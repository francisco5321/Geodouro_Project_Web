<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * @property int $user_id
 * @property bool $is_authenticated
 * @property string $guest_label
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $username
 * @property string|null $password_hash
 * @property string|null $auth_key
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Observation[] $observations
 * @property Publication[] $publications
 */
class AppUser extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%app_user}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['guest_label'], 'required'],
            [['is_authenticated'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['guest_label', 'first_name', 'last_name', 'email', 'username', 'password_hash'], 'string'],
            [['auth_key'], 'string', 'max' => 64],
            [['email'], 'email'],
            [['email', 'username', 'guest_label'], 'unique'],
            [['guest_label'], 'filter', 'filter' => 'trim'],
            [['username', 'first_name', 'last_name'], 'filter', 'filter' => 'trim'],
            [['guest_label'], 'validateGuestLabel'],
            [['first_name', 'last_name', 'email', 'username'], 'validateAuthenticatedFields'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'user_id' => 'ID',
            'is_authenticated' => 'Autenticado',
            'guest_label' => 'Identificador Guest',
            'first_name' => 'Primeiro Nome',
            'last_name' => 'Ultimo Nome',
            'email' => 'Email',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    public function validateGuestLabel(string $attribute): void
    {
        if (trim((string) $this->$attribute) === '') {
            $this->addError($attribute, 'O guest label nao pode estar vazio.');
        }
    }

    public function validateAuthenticatedFields(): void
    {
        if (!$this->is_authenticated) {
            return;
        }

        foreach (['first_name', 'last_name', 'email', 'username'] as $attribute) {
            if (trim((string) $this->$attribute) === '') {
                $this->addError($attribute, 'Este campo e obrigatorio para utilizadores autenticados.');
            }
        }
    }

    public function getFullName(): string
    {
        $fullName = trim(sprintf('%s %s', $this->first_name, $this->last_name));

        return $fullName !== '' ? $fullName : ($this->username ?: $this->guest_label);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function validatePassword(string $password): bool
    {
        return $this->password_hash !== null
            && Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getObservations(): ActiveQuery
    {
        return $this->hasMany(Observation::class, ['user_id' => 'user_id']);
    }

    public function getPublications(): ActiveQuery
    {
        return $this->hasMany(Publication::class, ['user_id' => 'user_id']);
    }

    public static function findIdentity($id): ?self
    {
        return static::findOne(['user_id' => $id, 'is_authenticated' => true]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public static function findByUsername(string $username): ?self
    {
        return static::find()
            ->andWhere(['username' => trim($username), 'is_authenticated' => true])
            ->one();
    }

    public function getId(): int
    {
        return $this->user_id;
    }

    public function getAuthKey(): ?string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }
}
