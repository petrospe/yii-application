<?php

use sxoli\cms\models\Comment;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;

/** @var yii\data\ActiveDataProvider $provider */
/** @var string|null $status */

$this->title = Yii::t('cms', 'Comments');
$tabs = ['' => Yii::t('cms', 'All')] + Comment::statusLabels();
?>
<div class="page-head"><h1><?= Html::encode($this->title) ?></h1></div>
<div class="filters">
    <?php foreach ($tabs as $value => $caption): ?>
        <?= Html::a(Html::encode($caption), ['index', 'status' => $value ?: null], ['class' => (string) $status === (string) $value ? 'active' : null]) ?>
    <?php endforeach ?>
</div>
<div class="panel">
    <?php foreach ($provider->getModels() as $comment): ?>
        <article style="border-bottom:1px solid var(--line);padding:12px 0;">
            <strong><?= Html::encode($comment->author_name) ?></strong>
            <span class="help"><?= Html::encode($comment->author_email) ?> · <?= Yii::$app->formatter->asDatetime($comment->created_at) ?></span>
            <span class="status <?= Html::encode($comment->status) ?>"><?= Html::encode(Comment::statusLabels()[$comment->status] ?? $comment->status) ?></span>
            <p><?= $comment->body ?></p>
            <p class="help mb-1"><?= Html::encode($comment->content->title ?? '') ?></p>
            <?= Html::a(Yii::t('cms', 'Approve'), ['status', 'id' => $comment->id, 'status' => Comment::STATUS_APPROVED], ['data' => ['method' => 'post']]) ?>
            · <?= Html::a(Yii::t('cms', 'Spam'), ['status', 'id' => $comment->id, 'status' => Comment::STATUS_SPAM], ['data' => ['method' => 'post']]) ?>
            · <?= Html::a(Yii::t('cms', 'Trash'), ['status', 'id' => $comment->id, 'status' => Comment::STATUS_TRASH], ['data' => ['method' => 'post']]) ?>
            · <?= Html::a(Yii::t('cms', 'Delete'), ['delete', 'id' => $comment->id], ['data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Delete this comment?')]]) ?>
        </article>
    <?php endforeach ?>
    <?php if ($provider->getCount() === 0): ?>
        <p class="help mb-0"><?= Html::encode(Yii::t('cms', 'No comments yet.')) ?></p>
    <?php endif ?>
</div>
<?= LinkPager::widget(['pagination' => $provider->pagination]) ?>
