<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\models\Comment;
use sxoli\cms\models\Content;
use sxoli\cms\models\Media;
use sxoli\cms\models\Setting;

class DashboardController extends AdminController
{
    public function actionIndex()
    {
        try {
            $ready = Setting::find()->exists();
        } catch (\Throwable $e) {
            $ready = false;
        }
        if (!$ready) {
            return $this->render('install');
        }
        return $this->render('index', [
            'posts' => (int) Content::find()->where(['type' => Content::TYPE_POST])->andWhere(['<>', 'status', Content::STATUS_TRASH])->count(),
            'pages' => (int) Content::find()->where(['type' => Content::TYPE_PAGE])->andWhere(['<>', 'status', Content::STATUS_TRASH])->count(),
            'pending' => (int) Comment::find()->where(['status' => Comment::STATUS_PENDING])->count(),
            'media' => (int) Media::find()->count(),
            'recent' => Content::find()->with('author')->orderBy(['updated_at' => SORT_DESC])->limit(6)->all(),
            'comments' => Comment::find()->with('content')->orderBy(['created_at' => SORT_DESC])->limit(6)->all(),
        ]);
    }
}
