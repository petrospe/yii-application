<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\Languages;
use sxoli\cms\models\Translation;
use sxoli\cms\models\Widget;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class WidgetController extends AdminController
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
        if (!Yii::$app->user->can('manageAppearance')) {
            throw new ForbiddenHttpException();
        }
        return true;
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'widgets' => Widget::find()->orderBy(['sidebar' => SORT_ASC, 'position' => SORT_ASC])->all(),
        ]);
    }

    public function actionCreate()
    {
        $model = new Widget(['enabled' => 1, 'position' => 0, 'type' => 'search', 'sidebar' => 'sidebar']);
        if ($this->save($model)) {
            return $this->redirect(['index']);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->find($id);
        if ($this->save($model)) {
            return $this->redirect(['index']);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionDelete(int $id)
    {
        $this->find($id)->delete();
        return $this->redirect(['index']);
    }

    private function save(Widget $model): bool
    {
        $post = Yii::$app->request->post();
        if (!isset($post['Widget'])) {
            return false;
        }
        $model->load($post);
        $model->enabled = isset($post['Widget']['enabled']) && (string) $post['Widget']['enabled'] !== '0' ? 1 : 0;
        if ($model->save()) {
            $error = Translation::storeRequest('widget', (int) $model->id, Languages::defaultCode());
            Yii::$app->session->setFlash($error ? 'danger' : 'success', $error ?: Yii::t('cms', 'Saved.'));
            return true;
        }
        return false;
    }

    private function find(int $id): Widget
    {
        $model = Widget::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
