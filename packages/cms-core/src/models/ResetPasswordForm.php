<?php

namespace sxoli\cms\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    public string $password = '';

    private User $_user;

    public function __construct(string $token, $config = [])
    {
        if ($token === '' || ($user = User::findByPasswordResetToken($token)) === null) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }
        $this->_user = $user;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'min' => 8],
        ];
    }

    public function attributeLabels(): array
    {
        return ['password' => Yii::t('cms', 'Password')];
    }

    public function resetPassword(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();
        return $user->save(false);
    }
}
