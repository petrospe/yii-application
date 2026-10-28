<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\models\LoginForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class AuthController extends Controller
{
    public $layout = 'auth';

    public function actions(): array
    {
        return [
            'error' => ['class' => \yii\web\ErrorAction::class],
        ];
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['login', 'error'], 'roles' => ['?', '@']],
                    ['allow' => true, 'actions' => ['logout'], 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['logout' => ['post']],
            ],
        ];
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/cms/dashboard/index']);
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if (!Yii::$app->user->can('accessAdmin')) {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('danger', Yii::t('cms', 'This account cannot open the administration area.'));
                return $this->redirect(['login']);
            }
            return $this->goBack(['/cms/dashboard/index']);
        }
        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['login']);
    }
}
