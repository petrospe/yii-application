<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var sxoli\cms\models\LoginForm $model */

$this->title = Yii::t('cms', 'Sign in');
?>
<div class="layout single">
    <div class="panel">
        <h1><?= Html::encode($this->title) ?></h1>
        <?php $form = ActiveForm::begin() ?>
        <?= $form->field($model, 'username') ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?= $form->field($model, 'rememberMe')->checkbox() ?>
        <button class="btn" type="submit"><?= Html::encode(Yii::t('cms', 'Sign in')) ?></button>
        <?php ActiveForm::end() ?>
        <p><?= Html::a(Yii::t('cms', 'Create an account'), ['signup']) ?></p>
        <p><?= Html::a(Yii::t('cms', 'Forgot password?'), ['request-password-reset']) ?></p>
    </div>
</div>
