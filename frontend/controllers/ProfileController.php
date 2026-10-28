<?php

namespace frontend\controllers;

use sxoli\cms\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class ProfileController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['editOwnProfile']],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        /** @var User $model */
        $model = Yii::$app->user->identity;
        $model->scenario = 'update';
        $model->password = null;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Saved.'));
            return $this->refresh();
        }
        return $this->render('index', ['model' => $model]);
    }
}
