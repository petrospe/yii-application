<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\Languages;
use sxoli\cms\models\Menu;
use sxoli\cms\models\MenuItem;
use sxoli\cms\models\Translation;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class MenuController extends AdminController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'delete-item' => ['post'],
                    'move' => ['post'],
                ],
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
        return $this->render('index', ['menus' => Menu::find()->with('items')->all()]);
    }

    public function actionCreate()
    {
        $model = new Menu();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->id]);
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->find($id);
        $labels = Yii::$app->request->post('ItemLabel', []);
        if (is_array($labels) && $labels !== []) {
            foreach ($labels as $language => $items) {
                if (!Languages::isSupported((string) $language) || $language === Languages::defaultCode() || !is_array($items)) {
                    continue;
                }
                foreach ($items as $itemId => $label) {
                    $menuItem = MenuItem::findOne(['id' => (int) $itemId, 'menu_id' => $model->id]);
                    if ($menuItem === null) {
                        continue;
                    }
                    Translation::store('menu_item', (int) $menuItem->id, (string) $language, [
                        'title' => trim((string) $label),
                    ]);
                }
            }
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Saved.'));
            return $this->redirect(['update', 'id' => $model->id]);
        }
        $item = new MenuItem(['menu_id' => $model->id, 'type' => 'custom', 'target' => '_self']);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Saved.'));
            return $this->redirect(['update', 'id' => $model->id]);
        }
        if (isset(Yii::$app->request->post()['MenuItem'])) {
            $item->load(Yii::$app->request->post());
            $ref = (string) Yii::$app->request->post('target_ref', '');
            if ($item->type !== 'custom' && str_contains($ref, ':')) {
                [$kind, $objectId] = explode(':', $ref, 2);
                $item->type = $kind === 'term' ? 'term' : 'content';
                $item->object_id = (int) $objectId;
                $item->url = null;
            }
            $max = (int) MenuItem::find()->where(['menu_id' => $model->id])->max('sort_order');
            $item->sort_order = $max + 1;
            if ($item->save()) {
                return $this->redirect(['update', 'id' => $model->id]);
            }
        }
        return $this->render('update', ['model' => $model, 'item' => $item]);
    }

    public function actionDelete(int $id)
    {
        $this->find($id)->delete();
        return $this->redirect(['index']);
    }

    public function actionDeleteItem(int $id)
    {
        $item = MenuItem::findOne($id);
        if ($item === null) {
            throw new NotFoundHttpException();
        }
        $menuId = $item->menu_id;
        $item->delete();
        return $this->redirect(['update', 'id' => $menuId]);
    }

    public function actionMove(int $id, string $direction)
    {
        $item = MenuItem::findOne($id);
        if ($item === null) {
            throw new NotFoundHttpException();
        }
        $query = MenuItem::find()->where([
            'menu_id' => $item->menu_id,
            'parent_id' => $item->parent_id,
        ]);
        if ($direction === 'up') {
            $swap = (clone $query)->andWhere(['<', 'sort_order', $item->sort_order])->orderBy(['sort_order' => SORT_DESC])->one();
        } else {
            $swap = (clone $query)->andWhere(['>', 'sort_order', $item->sort_order])->orderBy(['sort_order' => SORT_ASC])->one();
        }
        if ($swap !== null) {
            $current = $item->sort_order;
            $item->sort_order = $swap->sort_order;
            $swap->sort_order = $current;
            $item->save(false);
            $swap->save(false);
        }
        return $this->redirect(['update', 'id' => $item->menu_id]);
    }

    private function find(int $id): Menu
    {
        $model = Menu::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
