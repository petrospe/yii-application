<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var sxoli\cms\models\PasswordResetRequestForm $model */

$this->title = Yii::t('cms', 'Forgot password?');
?>
<div class="layout single">
    <div class="panel">
        <h1><?= Html::encode($this->title) ?></h1>
        <?php $form = ActiveForm::begin() ?>
        <?= $form->field($model, 'email') ?>
        <button class="btn" type="submit"><?= Html::encode(Yii::t('cms', 'Send reset link')) ?></button>
        <?php ActiveForm::end() ?>
    </div>
</div>
