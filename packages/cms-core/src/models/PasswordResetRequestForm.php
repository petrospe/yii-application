<?php

namespace sxoli\cms\models;

use Yii;
use yii\base\Model;

class PasswordResetRequestForm extends Model
{
    public string $email = '';

    public function rules(): array
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist', 'targetClass' => User::class, 'filter' => ['status_id' => User::STATUS_ACTIVE], 'targetAttribute' => 'email'],
        ];
    }

    public function attributeLabels(): array
    {
        return ['email' => Yii::t('cms', 'Email')];
    }

    public function sendEmail(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $user = User::findOne(['email' => $this->email, 'status_id' => User::STATUS_ACTIVE]);
        if ($user === null) {
            return false;
        }
        if (!User::isPasswordResetTokenValid((string) $user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save(false)) {
                return false;
            }
        }
        $from = Setting::get('admin_email', Yii::$app->params['supportEmail'] ?? 'noreply@example.com');
        return Yii::$app->mailer
            ->compose(['html' => '@common/mail/passwordResetToken-html', 'text' => '@common/mail/passwordResetToken-text'], ['user' => $user])
            ->setFrom([$from => Yii::$app->name])
            ->setTo($this->email)
            ->setSubject(Yii::t('cms', 'Password reset for {name}', ['name' => Yii::$app->name]))
            ->send();
    }
}
