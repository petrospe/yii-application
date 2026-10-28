<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\models\Media;
use sxoli\cms\services\MediaStorage;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class MediaController extends AdminController
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

    public function actionIndex()
    {
        $provider = new ActiveDataProvider([
            'query' => Media::find()->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 24],
        ]);
        return $this->render('index', ['provider' => $provider]);
    }

    public function actionUpload()
    {
        $this->assertUpload();
        $file = UploadedFile::getInstanceByName('file');
        $wantsJson = Yii::$app->request->isAjax || str_contains((string) Yii::$app->request->headers->get('Accept'), 'json');
        if ($file === null) {
            if ($wantsJson) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['error' => Yii::t('cms', 'Choose a file.')];
            }
            Yii::$app->session->setFlash('danger', Yii::t('cms', 'Choose a file.'));
            return $this->redirect(['index']);
        }
        try {
            $title = trim((string) Yii::$app->request->post('title', ''));
            $media = MediaStorage::store($file, (int) Yii::$app->user->id, $title !== '' ? $title : null);
            $alt = trim((string) Yii::$app->request->post('alt', ''));
            if ($alt !== '') {
                $media->alt = $alt;
                $media->save(false);
            }
        } catch (\Throwable $e) {
            if ($wantsJson) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                Yii::$app->response->statusCode = 422;
                return ['error' => $e->getMessage()];
            }
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(['index']);
        }
        if ($wantsJson) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['location' => $media->getUrl(), 'id' => $media->id];
        }
        Yii::$app->session->setFlash('success', Yii::t('cms', 'Uploaded.'));
        return $this->redirect(['index']);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->find($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Saved.'));
            return $this->redirect(['index']);
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id)
    {
        $this->assertUpload();
        $this->find($id)->delete();
        Yii::$app->session->setFlash('success', Yii::t('cms', 'Deleted.'));
        return $this->redirect(['index']);
    }

    private function assertUpload(): void
    {
        if (!Yii::$app->user->can('uploadMedia')) {
            throw new ForbiddenHttpException();
        }
    }

    private function find(int $id): Media
    {
        $model = Media::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
