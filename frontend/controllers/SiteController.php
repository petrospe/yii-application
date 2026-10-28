<?php

namespace frontend\controllers;

use sxoli\cms\models\LoginForm;
use sxoli\cms\models\PasswordResetRequestForm;
use sxoli\cms\models\ResetPasswordForm;
use sxoli\cms\models\SignupForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class SiteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    ['actions' => ['signup'], 'allow' => true, 'roles' => ['?']],
                    ['actions' => ['logout'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['logout' => ['post']],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => ['class' => \yii\web\ErrorAction::class],
        ];
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && ($user = $model->signup())) {
            Yii::$app->user->login($user, 0);
            return $this->goHome();
        }
        return $this->render('signup', ['model' => $model]);
    }

    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->sendEmail()) {
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Check your email for a reset link.'));
            return $this->goHome();
        }
        return $this->render('requestPasswordReset', ['model' => $model]);
    }

    public function actionResetPassword(string $token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->load(Yii::$app->request->post()) && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Your password has been changed.'));
            return $this->redirect(['login']);
        }
        return $this->render('resetPassword', ['model' => $model]);
    }
}
