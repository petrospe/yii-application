<?php

use sxoli\cms\services\WidgetRenderer;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var sxoli\cms\models\Content $model */

$this->title = $model->text('meta_title') ?: $model->text('title');
$this->params['description'] = $model->text('meta_description') ?: $model->text('excerpt');
$this->registerLinkTag(['rel' => 'canonical', 'href' => Url::to($model->getUrl(), true)]);
?>
<div class="layout">
    <article class="entry">
        <h1><?= Html::encode($model->text('title')) ?></h1>
        <p class="meta">
            <?= Yii::$app->formatter->asDatetime($model->published_at) ?>
            · <?= Html::encode($model->author->displayLabel ?? '') ?>
            <?php foreach ($model->getCategories() as $category): ?>
                · <a href="<?= Html::encode($category->getUrl()) ?>"><?= Html::encode($category->text('name')) ?></a>
            <?php endforeach ?>
        </p>
        <?php if ($model->featuredMedia && $model->featuredMedia->isImage()): ?>
            <img class="cover" src="<?= Html::encode($model->featuredMedia->getUrl()) ?>" alt="<?= Html::encode((string) $model->featuredMedia->alt) ?>">
        <?php endif ?>
        <div class="entry-body"><?= $model->renderedBody() ?></div>
        <?= $this->render('_comments', ['model' => $model]) ?>
    </article>
    <aside><?= WidgetRenderer::renderSidebar('sidebar') ?></aside>
</div>
