<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var sxoli\cms\models\SignupForm $model */

$this->title = Yii::t('cms', 'Create an account');
?>
<div class="layout single">
    <div class="panel">
        <h1><?= Html::encode($this->title) ?></h1>
        <?php $form = ActiveForm::begin() ?>
        <?= $form->field($model, 'display_name') ?>
        <?= $form->field($model, 'username') ?>
        <?= $form->field($model, 'email') ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <button class="btn" type="submit"><?= Html::encode(Yii::t('cms', 'Create an account')) ?></button>
        <?php ActiveForm::end() ?>
    </div>
</div>
