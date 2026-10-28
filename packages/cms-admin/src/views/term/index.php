<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var sxoli\cms\models\Taxonomy $taxonomy */
/** @var sxoli\cms\models\Term $model */
/** @var sxoli\cms\models\Term[] $terms */

$this->title = $taxonomy->label;
$parents = ['' => Yii::t('cms', 'None')];
foreach ($terms as $term) {
    $parents[$term->id] = $term->name;
}
?>
<div class="page-head"><h1><?= Html::encode($taxonomy->label) ?></h1></div>
<div class="layout-form">
    <div class="panel">
        <table class="cms-table">
            <thead><tr><th><?= Html::encode(Yii::t('cms', 'Name')) ?></th><th><?= Html::encode(Yii::t('cms', 'Slug')) ?></th><th><?= Html::encode(Yii::t('cms', 'Count')) ?></th><th></th></tr></thead>
            <?php foreach ($terms as $term): ?>
                <tr>
                    <td><?= Html::a(Html::encode($term->name), ['update', 'id' => $term->id]) ?></td>
                    <td><?= Html::encode($term->slug) ?></td>
                    <td><?= (int) $term->count ?></td>
                    <td><?= Html::a(Yii::t('cms', 'Delete'), ['delete', 'id' => $term->id], ['data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Delete this term?')]]) ?></td>
                </tr>
            <?php endforeach ?>
        </table>
    </div>
    <div class="panel">
        <h2><?= Html::encode(Yii::t('cms', 'Add new')) ?></h2>
        <?php $form = ActiveForm::begin() ?>
        <?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'slug') ?>
        <?php if ($taxonomy->hierarchical): ?>
            <?= $form->field($model, 'parent_id')->dropDownList($parents) ?>
        <?php endif ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
        <?= Html::submitButton(Yii::t('cms', 'Save'), ['class' => 'btn btn-accent']) ?>
        <?php ActiveForm::end() ?>
    </div>
</div>
