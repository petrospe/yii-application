<?php

use sxoli\cms\models\Term;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var sxoli\cms\models\Term $model */

$this->title = $model->name;
$parents = ['' => Yii::t('cms', 'None')];
foreach (Term::forTaxonomy($model->taxonomy->name) as $term) {
    if ((int) $term->id !== (int) $model->id) {
        $parents[$term->id] = $term->name;
    }
}
?>
<div class="panel" style="max-width:640px;">
    <h1><?= Html::encode($model->name) ?></h1>
    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'slug') ?>
    <?php if ($model->taxonomy->hierarchical): ?>
        <?= $form->field($model, 'parent_id')->dropDownList($parents) ?>
    <?php endif ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
    <?= $this->render('/_translation', [
        'entity' => 'term',
        'id' => (int) $model->id,
        'sourceLanguage' => \sxoli\cms\Languages::defaultCode(),
        'fields' => [
            'title' => Yii::t('cms', 'Name'),
            'slug' => Yii::t('cms', 'Slug'),
            'description' => Yii::t('cms', 'Description'),
        ],
        'multiline' => ['description'],
    ]) ?>
    <?= Html::submitButton(Yii::t('cms', 'Save'), ['class' => 'btn btn-accent']) ?>
    <?php ActiveForm::end() ?>
</div>
