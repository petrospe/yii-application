<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var sxoli\cms\models\User $model */

$this->title = Yii::t('cms', 'Profile');
?>
<div class="layout single">
    <div class="panel">
        <h1><?= Html::encode($this->title) ?></h1>
        <?php $form = ActiveForm::begin() ?>
        <?= $form->field($model, 'display_name') ?>
        <?= $form->field($model, 'email') ?>
        <?= $form->field($model, 'bio')->textarea(['rows' => 4]) ?>
        <?= $form->field($model, 'password')->passwordInput()->hint(Yii::t('cms', 'Leave empty to keep the current password.')) ?>
        <button class="btn" type="submit"><?= Html::encode(Yii::t('cms', 'Save')) ?></button>
        <?php ActiveForm::end() ?>
    </div>
</div>
