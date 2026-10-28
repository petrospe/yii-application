<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var sxoli\cms\models\ResetPasswordForm $model */

$this->title = Yii::t('cms', 'Choose a new password');
?>
<div class="layout single">
    <div class="panel">
        <h1><?= Html::encode($this->title) ?></h1>
        <?php $form = ActiveForm::begin() ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <button class="btn" type="submit"><?= Html::encode(Yii::t('cms', 'Save')) ?></button>
        <?php ActiveForm::end() ?>
    </div>
</div>
