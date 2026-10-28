<?php

namespace sxoli\cms\models;

use Yii;
use yii\base\Model;

class SignupForm extends Model
{
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $display_name = '';

    public function rules(): array
    {
        return [
            [['username', 'email', 'password'], 'required'],
            [['username', 'email', 'display_name'], 'trim'],
            ['username', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'username'],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9._-]+$/'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email'],
            ['password', 'string', 'min' => 8],
            ['display_name', 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('cms', 'Username'),
            'email' => Yii::t('cms', 'Email'),
            'password' => Yii::t('cms', 'Password'),
            'display_name' => Yii::t('cms', 'Display name'),
        ];
    }

    public function signup(): ?User
    {
        if (!$this->validate()) {
            return null;
        }
        $user = new User(['scenario' => 'create']);
        $user->username = $this->username;
        $user->email = $this->email;
        $user->display_name = $this->display_name ?: $this->username;
        $user->password = $this->password;
        $user->role = 'subscriber';
        $user->status_id = User::STATUS_ACTIVE;
        if (!$user->save()) {
            return null;
        }
        $user->assignRole('subscriber');
        return $user;
    }
}
