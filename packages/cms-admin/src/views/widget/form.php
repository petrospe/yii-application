<?php

use sxoli\cms\models\Menu;
use sxoli\cms\models\Widget;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var Widget $model */

$this->title = $model->isNewRecord ? Yii::t('cms', 'Add new') : ($model->title ?: $model->type);
$types = [];
foreach (Yii::$app->cms->widgetTypes() as $id => $type) {
    $types[$id] = $type['label'];
}
?>
<div class="panel" style="max-width:720px;">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($model, 'title') ?>
    <?= $form->field($model, 'type')->dropDownList($types) ?>
    <?= $form->field($model, 'sidebar')->dropDownList(Widget::sidebars()) ?>
    <?= $form->field($model, 'position')->input('number') ?>
    <?= $form->field($model, 'enabled')->checkbox() ?>
    <?= $form->field($model, 'limit')->input('number') ?>
    <?= $form->field($model, 'location')->dropDownList(Menu::locations()) ?>
    <?= $form->field($model, 'body')->textarea(['rows' => 4]) ?>
    <?= $form->field($model, 'message')->textarea(['rows' => 3]) ?>
    <?= $this->render('/_translation', [
        'entity' => 'widget',
        'id' => (int) $model->id,
        'sourceLanguage' => \sxoli\cms\Languages::defaultCode(),
        'fields' => [
            'title' => Yii::t('cms', 'Title'),
            'body' => Yii::t('cms', 'Text'),
            'excerpt' => Yii::t('cms', 'Message'),
        ],
        'multiline' => ['body', 'excerpt'],
    ]) ?>
    <?= Html::submitButton(Yii::t('cms', 'Save'), ['class' => 'btn btn-accent']) ?>
    <?php ActiveForm::end() ?>
</div>
