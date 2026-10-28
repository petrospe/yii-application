<?php

use sxoli\cms\models\User;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var User $model */

$this->title = $model->isNewRecord ? Yii::t('cms', 'Add new') : $model->username;
?>
<div class="panel" style="max-width:720px;">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'display_name') ?>
    <?= $form->field($model, 'email') ?>
    <?= $form->field($model, 'password')->passwordInput()->hint($model->isNewRecord ? '' : Yii::t('cms', 'Leave empty to keep the current password.')) ?>
    <?= $form->field($model, 'role')->dropDownList(User::roleList()) ?>
    <?= $form->field($model, 'status_id')->dropDownList(User::statusList()) ?>
    <?= $form->field($model, 'bio')->textarea(['rows' => 4]) ?>
    <?= Html::submitButton(Yii::t('cms', 'Save'), ['class' => 'btn btn-accent']) ?>
    <?php ActiveForm::end() ?>
</div>
