<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var sxoli\cms\models\Menu $model */

$this->title = Yii::t('cms', 'Add new');
?>
<div class="panel" style="max-width:640px;">
    <h1><?= Html::encode(Yii::t('cms', 'New menu')) ?></h1>
    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'location')->dropDownList(\sxoli\cms\models\Menu::locations(), ['prompt' => Yii::t('cms', 'None')]) ?>
    <?= Html::submitButton(Yii::t('cms', 'Save'), ['class' => 'btn btn-accent']) ?>
    <?php ActiveForm::end() ?>
</div>
