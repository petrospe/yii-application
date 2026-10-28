<?php

namespace sxoli\cms\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property int $role_id
 * @property int $status_id
 * @property int $user_type_id
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $display_name
 * @property string|null $bio
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_DISABLED = 4;

    public const ROLES = ['admin', 'editor', 'author', 'contributor', 'subscriber'];

    public ?string $password = null;
    public ?string $role = null;

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['username', 'email'], 'required'],
            [['username', 'email', 'display_name'], 'trim'],
            [['username', 'email', 'display_name'], 'string', 'max' => 255],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9._-]+$/'],
            ['username', 'unique'],
            ['email', 'email'],
            ['email', 'unique'],
            ['bio', 'string'],
            ['status_id', 'default', 'value' => self::STATUS_ACTIVE],
            ['status_id', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DISABLED]],
            ['password', 'required', 'on' => 'create'],
            ['password', 'string', 'min' => 8],
            ['role', 'in', 'range' => self::ROLES],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('cms', 'Username'),
            'email' => Yii::t('cms', 'Email'),
            'display_name' => Yii::t('cms', 'Display name'),
            'password' => Yii::t('cms', 'Password'),
            'status_id' => Yii::t('cms', 'Status'),
            'role' => Yii::t('cms', 'Role'),
            'bio' => Yii::t('cms', 'Bio'),
            'created_at' => Yii::t('cms', 'Registered'),
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert && !$this->auth_key) {
            $this->generateAuthKey();
        }
        if ($this->password !== null && $this->password !== '') {
            $this->setPassword($this->password);
        }
        if (!$this->display_name) {
            $this->display_name = $this->username;
        }
        if ($insert && !$this->status_id) {
            $this->status_id = self::STATUS_ACTIVE;
        }
        if ($insert && !$this->role_id) {
            $this->role_id = $this->role === 'admin' ? 3 : 1;
        }
        if ($insert && !$this->user_type_id) {
            $this->user_type_id = 1;
        }
        return true;
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $roles = $this->getRoleNames();
        $this->role = $roles[0] ?? null;
    }

    public static function findIdentity($id): ?self
    {
        return static::findOne(['id' => $id, 'status_id' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        throw new NotSupportedException('Access tokens are not used.');
    }

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'status_id' => self::STATUS_ACTIVE]);
    }

    public static function findByPasswordResetToken(string $token): ?self
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status_id' => self::STATUS_ACTIVE,
        ]);
    }

    public static function isPasswordResetTokenValid(string $token): bool
    {
        if ($token === '') {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = (int) (Yii::$app->params['user.passwordResetTokenExpire'] ?? 3600);
        return $timestamp + $expire >= time();
    }

    public function getId(): int
    {
        return (int) $this->getPrimaryKey();
    }

    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generatePasswordResetToken(): void
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken(): void
    {
        $this->password_reset_token = null;
    }

    public function getDisplayLabel(): string
    {
        return $this->display_name ?: $this->username;
    }

    /** @return string[] */
    public function getRoleNames(): array
    {
        $auth = Yii::$app->authManager;
        if ($auth === null || !$this->id) {
            return [];
        }
        return array_keys($auth->getRolesByUser($this->id));
    }

    public function assignRole(string $roleName): void
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if ($role === null) {
            throw new \RuntimeException('Unknown role: ' . $roleName);
        }
        foreach (self::ROLES as $name) {
            $existing = $auth->getRole($name);
            if ($existing !== null && $auth->getAssignment($name, $this->id)) {
                $auth->revoke($existing, $this->id);
            }
        }
        $auth->assign($role, $this->id);
        $this->role_id = $roleName === 'admin' ? 3 : 1;
        $this->updateAttributes(['role_id' => $this->role_id]);
    }

    /** @return array<int, string> */
    public static function statusList(): array
    {
        return [
            self::STATUS_ACTIVE => Yii::t('cms', 'Active'),
            self::STATUS_DISABLED => Yii::t('cms', 'Disabled'),
        ];
    }

    /** @return array<string, string> */
    public static function roleList(): array
    {
        return [
            'admin' => Yii::t('cms', 'Administrator'),
            'editor' => Yii::t('cms', 'Editor'),
            'author' => Yii::t('cms', 'Author'),
            'contributor' => Yii::t('cms', 'Contributor'),
            'subscriber' => Yii::t('cms', 'Subscriber'),
        ];
    }
}
