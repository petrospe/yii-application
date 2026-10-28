<?php

use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var int $posts */
/** @var int $pages */
/** @var int $pending */
/** @var int $media */
/** @var sxoli\cms\models\Content[] $recent */
/** @var sxoli\cms\models\Comment[] $comments */

$this->title = Yii::t('cms', 'Dashboard');
?>
<div class="page-head">
    <div>
        <h1><?= Html::encode(Yii::t('cms', 'Dashboard')) ?></h1>
        <p class="help mb-0"><?= Html::encode((string) \sxoli\cms\models\Setting::get('tagline', '')) ?></p>
    </div>
    <?= Html::a(Yii::t('cms', 'Write a post'), ['/cms/content/create', 'type' => 'post'], ['class' => 'btn btn-accent']) ?>
</div>
<div class="stats">
    <div class="stat"><b><?= $posts ?></b><span><?= Html::encode(Yii::t('cms', 'Posts')) ?></span></div>
    <div class="stat"><b><?= $pages ?></b><span><?= Html::encode(Yii::t('cms', 'Pages')) ?></span></div>
    <div class="stat"><b><?= $pending ?></b><span><?= Html::encode(Yii::t('cms', 'Pending comments')) ?></span></div>
    <div class="stat"><b><?= $media ?></b><span><?= Html::encode(Yii::t('cms', 'Media')) ?></span></div>
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="panel">
            <h2><?= Html::encode(Yii::t('cms', 'Recent content')) ?></h2>
            <table class="cms-table">
                <?php foreach ($recent as $item): ?>
                    <tr>
                        <td><?= Html::a(Html::encode($item->title), ['/cms/content/update', 'id' => $item->id]) ?></td>
                        <td><span class="status <?= Html::encode($item->status) ?>"><?= Html::encode($item->status) ?></span></td>
                        <td><?= Html::encode($item->author->displayLabel ?? '') ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <h2><?= Html::encode(Yii::t('cms', 'Recent comments')) ?></h2>
            <?php foreach ($comments as $comment): ?>
                <p>
                    <strong><?= Html::encode($comment->author_name) ?></strong>
                    <span class="status <?= Html::encode($comment->status) ?>"><?= Html::encode($comment->status) ?></span><br>
                    <?= Html::encode(mb_substr(strip_tags($comment->body), 0, 120)) ?>
                </p>
            <?php endforeach ?>
            <?php if ($comments === []): ?>
                <p class="help"><?= Html::encode(Yii::t('cms', 'No comments yet.')) ?></p>
            <?php endif ?>
        </div>
    </div>
</div>
