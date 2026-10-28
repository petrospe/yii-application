<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $provider */

$this->title = Yii::t('cms', 'Media');
?>
<div class="page-head">
    <h1><?= Html::encode($this->title) ?></h1>
</div>
<div class="panel">
    <form method="post" enctype="multipart/form-data" action="<?= \yii\helpers\Url::to(['upload']) ?>" class="row g-2 align-items-end">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        <div class="col-md-4">
            <label class="form-label"><?= Html::encode(Yii::t('cms', 'File')) ?></label>
            <input class="form-control" type="file" name="file" required>
        </div>
        <div class="col-md-3">
            <label class="form-label"><?= Html::encode(Yii::t('cms', 'Title')) ?></label>
            <input class="form-control" name="title">
        </div>
        <div class="col-md-3">
            <label class="form-label"><?= Html::encode(Yii::t('cms', 'Alternative text')) ?></label>
            <input class="form-control" name="alt">
        </div>
        <div class="col-md-2">
            <button class="btn btn-accent w-100" type="submit"><?= Html::encode(Yii::t('cms', 'Upload')) ?></button>
        </div>
    </form>
</div>
<div class="media-grid">
    <?php foreach ($provider->getModels() as $media): ?>
        <article class="media-card">
            <?php if ($media->isImage()): ?>
                <img src="<?= Html::encode($media->getUrl()) ?>" alt="<?= Html::encode((string) $media->alt) ?>">
            <?php else: ?>
                <div class="file"><?= Html::encode(strtoupper(pathinfo($media->path, PATHINFO_EXTENSION))) ?></div>
            <?php endif ?>
            <div class="meta">
                <strong><?= Html::encode($media->title) ?></strong>
                <div class="help"><?= Yii::$app->formatter->asShortSize($media->size) ?></div>
                <?= Html::a(Yii::t('cms', 'Edit'), ['update', 'id' => $media->id]) ?>
                ·
                <?= Html::a(Yii::t('cms', 'Delete'), ['delete', 'id' => $media->id], [
                    'data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Delete this file?')],
                ]) ?>
            </div>
        </article>
    <?php endforeach ?>
</div>
<?= LinkPager::widget(['pagination' => $provider->pagination]) ?>
