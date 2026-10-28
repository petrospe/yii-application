<?php

use sxoli\cms\models\Content;
use sxoli\cms\models\Menu;
use sxoli\cms\models\Term;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var Menu $model */
/** @var sxoli\cms\models\MenuItem $item */

$this->title = $model->name;
$contents = [];
foreach (Content::find()->where(['status' => Content::STATUS_PUBLISH])->orderBy(['title' => SORT_ASC])->all() as $content) {
    $contents[$content->id] = $content->title . ' (' . $content->type . ')';
}
$terms = [];
foreach (array_merge(Term::forTaxonomy('category'), Term::forTaxonomy('post_tag')) as $term) {
    $terms[$term->id] = $term->name;
}
$parents = ['' => Yii::t('cms', 'None')];
foreach ($model->items as $existing) {
    $parents[$existing->id] = $existing->label;
}
?>
<div class="page-head"><h1><?= Html::encode($model->name) ?></h1></div>
<div class="layout-form">
    <div class="panel">
        <h2><?= Html::encode(Yii::t('cms', 'Items')) ?></h2>
        <?php foreach ($model->tree() as $node): ?>
            <?= $this->render('_item', ['item' => $node, 'depth' => 0]) ?>
        <?php endforeach ?>
        <?php if ($model->items !== []): ?>
            <form id="menu-translations" method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <button class="btn btn-accent mt-3" type="submit"><?= Html::encode(Yii::t('cms', 'Save translations')) ?></button>
            </form>
        <?php endif ?>
        <?php if ($model->items === []): ?>
            <p class="help"><?= Html::encode(Yii::t('cms', 'This menu is empty.')) ?></p>
        <?php endif ?>
    </div>
    <div>
        <div class="panel">
            <h2><?= Html::encode(Yii::t('cms', 'Menu settings')) ?></h2>
            <?php $form = ActiveForm::begin() ?>
            <?= $form->field($model, 'name') ?>
            <?= $form->field($model, 'location')->dropDownList(Menu::locations(), ['prompt' => Yii::t('cms', 'None')]) ?>
            <?= Html::submitButton(Yii::t('cms', 'Save'), ['class' => 'btn btn-accent']) ?>
            <?php ActiveForm::end() ?>
        </div>
        <div class="panel">
            <h2><?= Html::encode(Yii::t('cms', 'Add item')) ?></h2>
            <?php $form = ActiveForm::begin() ?>
            <?= $form->field($item, 'label') ?>
            <?= $form->field($item, 'type')->dropDownList([
                'custom' => Yii::t('cms', 'Custom link'),
                'content' => Yii::t('cms', 'Content'),
                'term' => Yii::t('cms', 'Category or tag'),
            ]) ?>
            <?= $form->field($item, 'url') ?>
            <div class="mb-3">
                <label class="form-label" for="target-ref"><?= Html::encode(Yii::t('cms', 'Content or term')) ?></label>
                <select class="form-select" id="target-ref" name="target_ref">
                    <option value=""><?= Html::encode(Yii::t('cms', 'Choose')) ?></option>
                    <optgroup label="<?= Html::encode(Yii::t('cms', 'Content')) ?>">
                        <?php foreach ($contents as $id => $label): ?>
                            <option value="content:<?= (int) $id ?>"><?= Html::encode($label) ?></option>
                        <?php endforeach ?>
                    </optgroup>
                    <optgroup label="<?= Html::encode(Yii::t('cms', 'Category or tag')) ?>">
                        <?php foreach ($terms as $id => $label): ?>
                            <option value="term:<?= (int) $id ?>"><?= Html::encode($label) ?></option>
                        <?php endforeach ?>
                    </optgroup>
                </select>
            </div>
            <?= $form->field($item, 'parent_id')->dropDownList($parents) ?>
            <?= $form->field($item, 'target')->dropDownList(['_self' => Yii::t('cms', 'Same tab'), '_blank' => Yii::t('cms', 'New tab')]) ?>
            <?= Html::submitButton(Yii::t('cms', 'Add'), ['class' => 'btn btn-accent']) ?>
            <?php ActiveForm::end() ?>
            <p class="help"><?= Html::encode(Yii::t('cms', 'For a custom link, fill in the URL. For content or a term, choose it below and leave the URL empty.')) ?></p>
        </div>
    </div>
</div>
