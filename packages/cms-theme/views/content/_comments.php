<?php

use sxoli\cms\models\Comment;
use sxoli\cms\models\Setting;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var sxoli\cms\models\Content $model */

if (!$model->commentsOpen() && Comment::find()->where(['content_id' => $model->id, 'status' => Comment::STATUS_APPROVED])->count() === 0) {
    return;
}
$comments = Comment::tree(Comment::find()
    ->where(['content_id' => $model->id, 'status' => Comment::STATUS_APPROVED])
    ->orderBy(['created_at' => SORT_ASC])
    ->all());
$render = function (array $nodes) use (&$render) {
    if ($nodes === []) {
        return '';
    }
    $html = '<div class="children">';
    foreach ($nodes as $comment) {
        $html .= '<div class="comment"><strong>' . Html::encode($comment->author_name) . '</strong>'
            . '<div class="meta">' . Yii::$app->formatter->asDatetime($comment->created_at) . '</div>'
            . $comment->body
            . $render($comment->children)
            . '</div>';
    }
    return $html . '</div>';
};
$formModel = new Comment(['content_id' => $model->id]);
if (!Yii::$app->user->isGuest) {
    $formModel->author_name = Yii::$app->user->identity->displayLabel;
    $formModel->author_email = Yii::$app->user->identity->email;
}
?>
<section class="comments" id="comments">
    <h2><?= Html::encode(Yii::t('cms', 'Comments')) ?></h2>
    <?= $render($comments) ?>
    <?php if ($model->commentsOpen()): ?>
        <?php if (Setting::get('comment_registration', '0') === '1' && Yii::$app->user->isGuest): ?>
            <p><?= Html::a(Yii::t('cms', 'Sign in to comment'), ['/site/login']) ?></p>
        <?php else: ?>
            <?php $form = ActiveForm::begin(['action' => ['/comment/create']]) ?>
            <?= Html::hiddenInput('content_id', $model->id) ?>
            <?= Html::hiddenInput('Comment[content_id]', $model->id) ?>
            <div class="honeypot">
                <label>Website</label>
                <input type="text" name="Comment[website_url]" tabindex="-1" autocomplete="off">
            </div>
            <?php if (Yii::$app->user->isGuest): ?>
                <?= $form->field($formModel, 'author_name') ?>
                <?= $form->field($formModel, 'author_email') ?>
                <?= $form->field($formModel, 'author_url') ?>
            <?php endif ?>
            <?= $form->field($formModel, 'body')->textarea(['rows' => 5]) ?>
            <button class="btn" type="submit"><?= Html::encode(Yii::t('cms', 'Post comment')) ?></button>
            <?php ActiveForm::end() ?>
        <?php endif ?>
    <?php endif ?>
</section>
