<?php

use sxoli\cms\services\WidgetRenderer;
use yii\widgets\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $provider */
/** @var string $heading */
/** @var string|null $description */
/** @var string|null $query */

$this->title = $heading;
$this->params['description'] = $description ?? '';
?>
<div class="layout">
    <div>
        <h1 style="font-family: Literata, Georgia, serif;"><?= Html::encode($heading) ?></h1>
        <?php if (!empty($query)): ?>
            <p class="meta"><?= Html::encode(Yii::t('cms', 'Results for "{q}"', ['q' => $query])) ?></p>
        <?php endif ?>
        <?php if (!empty($description)): ?>
            <p><?= Html::encode($description) ?></p>
        <?php endif ?>
        <?php foreach ($provider->getModels() as $post): ?>
            <article class="post">
                <?php if ($post->featuredMedia && $post->featuredMedia->isImage()): ?>
                    <a href="<?= Html::encode($post->getUrl()) ?>"><img class="cover" src="<?= Html::encode($post->featuredMedia->getUrl()) ?>" alt=""></a>
                <?php endif ?>
                <h2><a href="<?= Html::encode($post->getUrl()) ?>"><?= Html::encode($post->text('title')) ?></a></h2>
                <p class="meta">
                    <?= Yii::$app->formatter->asDate($post->published_at) ?>
                    · <?= Html::encode($post->author->displayLabel ?? '') ?>
                </p>
                <p><?= Html::encode($post->text('excerpt')) ?></p>
            </article>
        <?php endforeach ?>
        <?php if ($provider->getCount() === 0): ?>
            <p><?= Html::encode(Yii::t('cms', 'Nothing here yet.')) ?></p>
        <?php endif ?>
        <?= LinkPager::widget(['pagination' => $provider->pagination]) ?>
    </div>
    <aside><?= WidgetRenderer::renderSidebar('sidebar') ?></aside>
</div>
