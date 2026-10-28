<?php

namespace sxoli\cms\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public string $username = '';
    public string $password = '';
    public bool $rememberMe = true;

    private ?User $_user = null;

    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('cms', 'Username'),
            'password' => Yii::t('cms', 'Password'),
            'rememberMe' => Yii::t('cms', 'Remember me'),
        ];
    }

    public function validatePassword(string $attribute): void
    {
        if ($this->hasErrors()) {
            return;
        }
        $user = $this->getUser();
        if ($user === null || !$user->validatePassword($this->password)) {
            $this->addError($attribute, Yii::t('cms', 'Incorrect username or password.'));
        }
    }

    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
    }

    public function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }
        return $this->_user;
    }
}
