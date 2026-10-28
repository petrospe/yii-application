<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\ContentPolicy;
use sxoli\cms\Languages;
use sxoli\cms\models\Content;
use sxoli\cms\models\Translation;
use sxoli\cms\models\ContentRevision;
use sxoli\cms\services\MediaStorage;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ContentController extends AdminController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'trash' => ['post'],
                    'delete' => ['post'],
                    'restore-revision' => ['post'],
                ],
            ],
        ]);
    }

    public function actionIndex(string $type = Content::TYPE_POST, ?string $status = null)
    {
        $this->assertType($type);
        $query = Content::find()->where(['type' => $type])->with('author');
        if ($type === Content::TYPE_POST && !Yii::$app->user->can('editPosts')) {
            $query->andWhere(['author_id' => Yii::$app->user->id]);
        }
        if ($status !== null && $status !== '') {
            $query->andWhere(['status' => $status]);
        } else {
            $query->andWhere(['<>', 'status', Content::STATUS_TRASH]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['updated_at' => SORT_DESC]],
        ]);
        return $this->render('index', [
            'provider' => $provider,
            'type' => $type,
            'status' => $status,
        ]);
    }

    public function actionCreate(string $type = Content::TYPE_POST)
    {
        $this->assertType($type);
        if (!ContentPolicy::canCreate($type)) {
            throw new ForbiddenHttpException(Yii::t('cms', 'You cannot create this.'));
        }
        $model = new Content();
        $model->type = $type;
        $model->status = Content::STATUS_DRAFT;
        $model->comment_status = $type === Content::TYPE_PAGE ? 'closed' : 'open';
        $model->author_id = (int) Yii::$app->user->id;
        $model->language = Languages::defaultCode();
        if ($this->save($model)) {
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Saved.'));
            return $this->redirect(['update', 'id' => $model->id]);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->find($id);
        if (!ContentPolicy::canUpdate($model)) {
            throw new ForbiddenHttpException(Yii::t('cms', 'You cannot edit this.'));
        }
        if ($this->save($model)) {
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Saved.'));
            return $this->redirect(['update', 'id' => $model->id]);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionTrash(int $id)
    {
        $model = $this->find($id);
        if (!ContentPolicy::canDelete($model)) {
            throw new ForbiddenHttpException();
        }
        $model->status = Content::STATUS_TRASH;
        $model->save(false);
        Yii::$app->session->setFlash('success', Yii::t('cms', 'Moved to trash.'));
        return $this->redirect(['index', 'type' => $model->type]);
    }

    public function actionDelete(int $id)
    {
        $model = $this->find($id);
        if (!ContentPolicy::canDelete($model)) {
            throw new ForbiddenHttpException();
        }
        $type = $model->type;
        $model->delete();
        Yii::$app->session->setFlash('success', Yii::t('cms', 'Deleted.'));
        return $this->redirect(['index', 'type' => $type, 'status' => Content::STATUS_TRASH]);
    }

    public function actionRestoreRevision(int $id, int $revisionId)
    {
        $model = $this->find($id);
        if (!ContentPolicy::canUpdate($model)) {
            throw new ForbiddenHttpException();
        }
        $revision = ContentRevision::findOne(['id' => $revisionId, 'content_id' => $model->id]);
        if ($revision === null) {
            throw new NotFoundHttpException();
        }
        $model->title = $revision->title;
        $model->excerpt = $revision->excerpt;
        $model->body = $revision->body;
        $model->save(false);
        Yii::$app->session->setFlash('success', Yii::t('cms', 'Revision restored.'));
        return $this->redirect(['update', 'id' => $model->id]);
    }

    private function save(Content $model): bool
    {
        $post = Yii::$app->request->post();
        if (!isset($post['Content'])) {
            return false;
        }
        $model->load($post);
        if (!isset($post['Content']['categoryIds'])) {
            $model->categoryIds = [];
        }
        if (!ContentPolicy::canPublish($model) && in_array($model->status, [Content::STATUS_PUBLISH, Content::STATUS_PRIVATE], true)) {
            $model->status = Content::STATUS_PENDING;
        }
        $file = UploadedFile::getInstanceByName('featured');
        if ($file !== null) {
            try {
                $media = MediaStorage::store($file, (int) Yii::$app->user->id);
                $model->featured_media_id = $media->id;
            } catch (\Throwable $e) {
                $model->addError('featured_media_id', $e->getMessage());
                return false;
            }
        }
        if (!$model->save()) {
            return false;
        }
        $error = Translation::storeRequest('content', (int) $model->id, (string) $model->language);
        if ($error !== null) {
            Yii::$app->session->setFlash('danger', $error);
        }
        return true;
    }

    private function assertType(string $type): void
    {
        if (!isset(Yii::$app->cms->postTypes()[$type])) {
            throw new NotFoundHttpException();
        }
        if ($type === Content::TYPE_PAGE && !Yii::$app->user->can('editPages')) {
            throw new ForbiddenHttpException();
        }
    }

    private function find(int $id): Content
    {
        $model = Content::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
