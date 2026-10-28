<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\models\Comment;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class CommentController extends AdminController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['status' => ['post'], 'delete' => ['post']],
            ],
        ]);
    }

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->can('moderateComments')) {
            throw new ForbiddenHttpException();
        }
        return true;
    }

    public function actionIndex(?string $status = null)
    {
        $query = Comment::find()->with('content')->orderBy(['id' => SORT_DESC]);
        if ($status) {
            $query->andWhere(['status' => $status]);
        }
        return $this->render('index', [
            'provider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 30]]),
            'status' => $status,
        ]);
    }

    public function actionStatus(int $id, string $status)
    {
        $model = $this->find($id);
        if (!isset(Comment::statusLabels()[$status])) {
            throw new NotFoundHttpException();
        }
        $model->status = $status;
        $model->save(false);
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    public function actionDelete(int $id)
    {
        $this->find($id)->delete();
        Yii::$app->session->setFlash('success', Yii::t('cms', 'Deleted.'));
        return $this->redirect(['index']);
    }

    private function find(int $id): Comment
    {
        $model = Comment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
