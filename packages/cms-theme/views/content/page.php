<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var sxoli\cms\models\Content $model */

$this->title = $model->text('meta_title') ?: $model->text('title');
$this->params['description'] = $model->text('meta_description') ?: $model->text('excerpt');
$this->registerLinkTag(['rel' => 'canonical', 'href' => Url::to($model->getUrl(), true)]);
?>
<div class="layout single">
    <article class="entry">
        <?php if ($model->featuredMedia && $model->featuredMedia->isImage()): ?>
            <img class="cover" src="<?= Html::encode($model->featuredMedia->getUrl()) ?>" alt="<?= Html::encode((string) $model->featuredMedia->alt) ?>">
        <?php endif ?>
        <h1><?= Html::encode($model->text('title')) ?></h1>
        <div class="entry-body"><?= $model->renderedBody() ?></div>
        <?= $this->render('_comments', ['model' => $model]) ?>
    </article>
</div>
