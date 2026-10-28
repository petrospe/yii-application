<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\Languages;
use sxoli\cms\models\Taxonomy;
use sxoli\cms\models\Term;
use sxoli\cms\models\Translation;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class TermController extends AdminController
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
        if (!Yii::$app->user->can('editPosts') && !Yii::$app->user->can('editPages')) {
            throw new ForbiddenHttpException();
        }
        return true;
    }

    public function actionIndex(string $taxonomy = Taxonomy::CATEGORY)
    {
        $tax = $this->taxonomy($taxonomy);
        $model = new Term(['taxonomy_id' => $tax->id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Saved.'));
            return $this->redirect(['index', 'taxonomy' => $taxonomy]);
        }
        return $this->render('index', [
            'taxonomy' => $tax,
            'model' => $model,
            'terms' => Term::forTaxonomy($taxonomy),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->find($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $error = Translation::storeRequest('term', (int) $model->id, Languages::defaultCode());
            Yii::$app->session->setFlash($error ? 'danger' : 'success', $error ?: Yii::t('cms', 'Saved.'));
            return $this->redirect(['index', 'taxonomy' => $model->taxonomy->name]);
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id)
    {
        $model = $this->find($id);
        $name = $model->taxonomy->name;
        $model->delete();
        Term::refreshCounts();
        return $this->redirect(['index', 'taxonomy' => $name]);
    }

    private function taxonomy(string $name): Taxonomy
    {
        $tax = Taxonomy::findByName($name);
        if ($tax === null) {
            throw new NotFoundHttpException();
        }
        return $tax;
    }

    private function find(int $id): Term
    {
        $model = Term::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
