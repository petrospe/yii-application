<?php

namespace frontend\controllers;

use sxoli\cms\models\Content;
use sxoli\cms\models\Setting;
use sxoli\cms\models\Term;
use sxoli\cms\models\Taxonomy;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ContentController extends Controller
{
    public function actionIndex()
    {
        if (Setting::get('show_on_front', 'posts') === 'page') {
            $page = Content::findOne([
                'id' => (int) Setting::get('page_on_front', 0),
                'type' => Content::TYPE_PAGE,
                'status' => Content::STATUS_PUBLISH,
            ]);
            if ($page !== null) {
                $this->view->params['languageUrls'] = $page->languageUrls();
                return $this->render('page', ['model' => $page]);
            }
        }
        return $this->actionBlog();
    }

    public function actionBlog()
    {
        return $this->render('index', [
            'provider' => $this->posts(),
            'heading' => Yii::t('cms', 'Latest posts'),
        ]);
    }

    public function actionPost(string $slug, ?string $year = null, ?string $month = null)
    {
        return $this->show($this->readable($slug, Content::TYPE_POST), $slug);
    }

    public function actionPage(string $slug)
    {
        return $this->show($this->readable($slug, Content::TYPE_PAGE), $slug);
    }

    public function actionResolve(string $slug)
    {
        $post = Content::findBySlug(Content::TYPE_POST, $slug);
        if ($post !== null) {
            return $this->show($this->readable($slug, Content::TYPE_POST), $slug);
        }
        $page = Content::findBySlug(Content::TYPE_PAGE, $slug);
        if ($page !== null) {
            return $this->redirect($page->getUrl(), 301);
        }
        throw new NotFoundHttpException(Yii::t('cms', 'Page not found.'));
    }

    public function actionCategory(string $slug)
    {
        return $this->archive($slug, Taxonomy::CATEGORY);
    }

    public function actionTag(string $slug)
    {
        return $this->archive($slug, Taxonomy::TAG);
    }

    public function actionSearch(string $q = '')
    {
        $q = trim($q);
        $query = Content::findPublished()->andWhere(['type' => Content::TYPE_POST]);
        if (mb_strlen($q) >= 2) {
            $translatedIds = \sxoli\cms\models\Translation::find()
                ->select('entity_id')
                ->where(['entity' => 'content', 'language' => Yii::$app->language])
                ->andWhere(['or',
                    ['like', 'title', $q],
                    ['like', 'excerpt', $q],
                    ['like', 'body', $q],
                ]);
            $query->andWhere(['or',
                ['like', 'title', $q],
                ['like', 'excerpt', $q],
                ['like', 'body', $q],
                ['id' => $translatedIds],
            ]);
        } else {
            $query->andWhere('0=1');
        }
        return $this->render('index', [
            'provider' => $this->provider($query),
            'heading' => Yii::t('cms', 'Search results'),
            'query' => $q,
        ]);
    }

    public function actionFeed()
    {
        $posts = Content::findPublished()
            ->andWhere(['type' => Content::TYPE_POST])
            ->orderBy(['published_at' => SORT_DESC])
            ->limit(20)
            ->all();
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');
        return $this->renderPartial('feed', ['posts' => $posts]);
    }

    private function archive(string $slug, string $taxonomyName)
    {
        $taxonomy = Taxonomy::findByName($taxonomyName);
        $term = $taxonomy ? Term::findBySlug((int) $taxonomy->id, $slug) : null;
        if ($term === null) {
            throw new NotFoundHttpException(Yii::t('cms', 'Page not found.'));
        }
        if ($term->text('slug') !== $slug) {
            return $this->redirect($term->getUrl(), 301);
        }
        $query = Content::findPublished()
            ->andWhere(['type' => Content::TYPE_POST])
            ->innerJoin('{{%cms_content_term}} ct', 'ct.content_id = {{%cms_content}}.id')
            ->andWhere(['ct.term_id' => $term->id]);
        $this->view->params['languageUrls'] = $term->languageUrls();
        return $this->render('index', [
            'provider' => $this->provider($query),
            'heading' => $term->text('name'),
            'description' => $term->text('description'),
        ]);
    }

    private function posts(): ActiveDataProvider
    {
        $query = Content::findPublished()
            ->andWhere(['type' => Content::TYPE_POST])
            ->orderBy(['published_at' => SORT_DESC]);
        return $this->provider($query);
    }

    private function provider(\yii\db\ActiveQuery $query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => max(1, (int) Setting::get('posts_per_page', 10))],
            'sort' => false,
        ]);
    }

    private function readable(string $slug, string $type): Content
    {
        $model = Content::findBySlug($type, $slug);
        if ($model === null || $model->status === Content::STATUS_TRASH || $model->status === Content::STATUS_DRAFT || $model->status === Content::STATUS_PENDING) {
            throw new NotFoundHttpException(Yii::t('cms', 'Page not found.'));
        }
        if ($model->status === Content::STATUS_PRIVATE && (Yii::$app->user->isGuest || (int) Yii::$app->user->id !== (int) $model->author_id && !Yii::$app->user->can('editPosts') && !Yii::$app->user->can('editPages'))) {
            throw new NotFoundHttpException(Yii::t('cms', 'Page not found.'));
        }
        if ($model->status === Content::STATUS_PUBLISH && $model->published_at && strtotime((string) $model->published_at) > time()) {
            throw new NotFoundHttpException(Yii::t('cms', 'Page not found.'));
        }
        return $model;
    }

    private function show(Content $model, string $slug): mixed
    {
        if ($model->text('slug') !== $slug) {
            return $this->redirect($model->getUrl(), 301);
        }
        $view = $model->type === Content::TYPE_PAGE ? 'page' : 'post';
        $this->view->params['languageUrls'] = $model->languageUrls();
        return $this->render($view, ['model' => $model]);
    }
}
