<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var sxoli\cms\models\Media $model */

$this->title = $model->title;
?>
<div class="page-head"><h1><?= Html::encode($model->title) ?></h1></div>
<div class="layout-form">
    <div class="panel">
        <?php if ($model->isImage()): ?>
            <img src="<?= Html::encode($model->getUrl()) ?>" alt="" style="max-width:100%;border-radius:12px;">
        <?php else: ?>
            <p><a href="<?= Html::encode($model->getUrl()) ?>"><?= Html::encode($model->path) ?></a></p>
        <?php endif ?>
    </div>
    <div class="panel">
        <?php $form = ActiveForm::begin() ?>
        <?= $form->field($model, 'title') ?>
        <?= $form->field($model, 'alt') ?>
        <?= Html::submitButton(Yii::t('cms', 'Save'), ['class' => 'btn btn-accent']) ?>
        <?php ActiveForm::end() ?>
    </div>
</div>
