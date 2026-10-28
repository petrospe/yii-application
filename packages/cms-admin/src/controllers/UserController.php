<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class UserController extends AdminController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['post']],
            ],
        ]);
    }

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->can('manageUsers')) {
            throw new ForbiddenHttpException();
        }
        return true;
    }

    public function actionIndex()
    {
        $provider = new ActiveDataProvider([
            'query' => User::find()->orderBy(['id' => SORT_ASC]),
            'pagination' => ['pageSize' => 30],
        ]);
        return $this->render('index', ['provider' => $provider]);
    }

    public function actionCreate()
    {
        $model = new User(['scenario' => 'create', 'status_id' => User::STATUS_ACTIVE, 'role' => 'subscriber']);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->role) {
                $model->assignRole($model->role);
            }
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Saved.'));
            return $this->redirect(['index']);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->find($id);
        $model->scenario = 'update';
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->role) {
                $model->assignRole($model->role);
            }
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Saved.'));
            return $this->redirect(['index']);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionDelete(int $id)
    {
        if ((int) $id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('danger', Yii::t('cms', 'You cannot delete your own account.'));
            return $this->redirect(['index']);
        }
        try {
            $this->find($id)->delete();
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Deleted.'));
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('danger', Yii::t('cms', 'This user still owns content and cannot be deleted.'));
        }
        return $this->redirect(['index']);
    }

    private function find(int $id): User
    {
        $model = User::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
