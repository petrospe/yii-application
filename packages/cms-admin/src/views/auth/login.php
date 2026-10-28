<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var sxoli\cms\models\LoginForm $model */

$this->title = Yii::t('cms', 'Sign in');
?>
<div class="auth-card card">
    <h1 style="font-family: Literata, Georgia, serif;"><?= Html::encode(Yii::t('cms', 'Sign in')) ?></h1>
    <p class="help"><?= Html::encode(Yii::t('cms', 'Administration')) ?></p>
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= Html::encode($type === 'error' ? 'danger' : $type) ?>"><?= Html::encode(is_array($message) ? implode(' ', $message) : $message) ?></div>
    <?php endforeach ?>
    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= $form->field($model, 'rememberMe')->checkbox() ?>
    <?= Html::submitButton(Yii::t('cms', 'Sign in'), ['class' => 'btn btn-accent w-100']) ?>
    <?php ActiveForm::end() ?>
</div>
