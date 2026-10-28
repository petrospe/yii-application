<?php

namespace frontend\controllers;

use sxoli\cms\models\Comment;
use sxoli\cms\models\Content;
use sxoli\cms\models\Setting;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CommentController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['create' => ['post']],
            ],
        ];
    }

    public function actionCreate()
    {
        $content = Content::findOne((int) Yii::$app->request->post('content_id'));
        if ($content === null || !$content->commentsOpen()) {
            throw new NotFoundHttpException();
        }
        if (Setting::get('comment_registration', '0') === '1' && Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }
        $model = new Comment(['content_id' => $content->id]);
        $model->load(Yii::$app->request->post());
        if ($model->website_url !== '') {
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Your comment is waiting for approval.'));
            return $this->redirect($content->getUrl());
        }
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $model->user_id = (int) $user->id;
            $model->author_name = $user->displayLabel;
            $model->author_email = $user->email;
        }
        $model->status = Setting::get('comment_moderation', '1') === '1'
            ? Comment::STATUS_PENDING
            : Comment::STATUS_APPROVED;
        if ($model->save()) {
            $message = $model->status === Comment::STATUS_PENDING
                ? Yii::t('cms', 'Your comment is waiting for approval.')
                : Yii::t('cms', 'Comment published.');
            Yii::$app->session->setFlash('success', $message);
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('cms', 'The comment could not be saved.'));
        }
        return $this->redirect($content->getUrl() . '#comments');
    }
}
