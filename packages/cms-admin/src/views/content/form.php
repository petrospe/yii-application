<?php

use sxoli\cms\ContentPolicy;
use sxoli\cms\models\Content;
use sxoli\cms\models\Media;
use sxoli\cms\models\Term;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var Content $model */

$this->title = $model->isNewRecord ? Yii::t('cms', 'Add new') : $model->title;
$categories = [];
foreach (Term::forTaxonomy('category') as $term) {
    $categories[$term->id] = $term->name;
}
$pages = Content::find()->where(['type' => Content::TYPE_PAGE])->andWhere(['<>', 'id', (int) $model->id])->orderBy(['title' => SORT_ASC])->all();
$parentList = [];
foreach ($pages as $page) {
    $parentList[$page->id] = $page->title;
}
$media = Media::find()->orderBy(['id' => SORT_DESC])->limit(40)->all();
$mediaList = ['' => Yii::t('cms', 'None')];
foreach ($media as $file) {
    $mediaList[$file->id] = $file->title;
}
$published = $model->published_at ? date('Y-m-d\TH:i', strtotime((string) $model->published_at)) : '';
?>
<div class="page-head">
    <h1><?= Html::encode($this->title) ?></h1>
</div>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
<div class="layout-form">
    <div>
        <div class="panel">
            <?= $form->field($model, 'title')->textInput() ?>
            <?= $form->field($model, 'slug')->textInput()->hint(Yii::t('cms', 'Leave empty to build it from the title.')) ?>
            <?= $form->field($model, 'body')->textarea(['rows' => 16, 'class' => 'form-control cms-editor']) ?>
            <?= $form->field($model, 'excerpt')->textarea(['rows' => 3]) ?>
        </div>
        <div class="panel">
            <h2><?= Html::encode(Yii::t('cms', 'Search')) ?></h2>
            <?= $form->field($model, 'meta_title') ?>
            <?= $form->field($model, 'meta_description')->textarea(['rows' => 2]) ?>
        </div>
        <?= $this->render('/_translation', [
            'entity' => 'content',
            'id' => (int) $model->id,
            'sourceLanguage' => (string) ($model->language ?: \sxoli\cms\Languages::defaultCode()),
            'fields' => [
                'title' => Yii::t('cms', 'Title'),
                'slug' => Yii::t('cms', 'Slug'),
                'excerpt' => Yii::t('cms', 'Excerpt'),
                'body' => Yii::t('cms', 'Content'),
                'meta_title' => Yii::t('cms', 'SEO title'),
                'meta_description' => Yii::t('cms', 'SEO description'),
            ],
            'multiline' => ['excerpt', 'body', 'meta_description'],
        ]) ?>
        <?php if (!$model->isNewRecord && $model->revisions): ?>
            <div class="panel">
                <h2><?= Html::encode(Yii::t('cms', 'Revisions')) ?></h2>
                <ul class="mb-0">
                    <?php foreach (array_slice($model->revisions, 0, 8) as $revision): ?>
                        <li>
                            <?= Yii::$app->formatter->asDatetime($revision->created_at) ?>
                            — <?= Html::encode($revision->title) ?>
                            <?= Html::a(Yii::t('cms', 'Restore'), ['restore-revision', 'id' => $model->id, 'revisionId' => $revision->id], [
                                'data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Restore this revision?')],
                            ]) ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>
    </div>
    <div>
        <div class="panel">
            <?= $form->field($model, 'language')->dropDownList(\sxoli\cms\Languages::labels()) ?>
            <?= $form->field($model, 'status')->dropDownList(ContentPolicy::statusesFor($model)) ?>
            <div class="mb-3">
                <label class="form-label" for="published-at"><?= Html::encode(Yii::t('cms', 'Publish date')) ?></label>
                <input class="form-control" id="published-at" type="datetime-local" name="Content[published_at]" value="<?= Html::encode($published) ?>">
            </div>
            <?= $form->field($model, 'comment_status')->dropDownList([
                'open' => Yii::t('cms', 'Open'),
                'closed' => Yii::t('cms', 'Closed'),
            ]) ?>
            <?= Html::submitButton(Yii::t('cms', 'Save'), ['class' => 'btn btn-accent w-100']) ?>
        </div>
        <?php if ($model->type === Content::TYPE_PAGE): ?>
            <div class="panel">
                <?= $form->field($model, 'parent_id')->dropDownList($parentList, ['prompt' => Yii::t('cms', 'None')]) ?>
                <?= $form->field($model, 'menu_order')->input('number') ?>
            </div>
        <?php else: ?>
            <div class="panel">
                <?= $form->field($model, 'categoryIds')->checkboxList($categories) ?>
                <?= $form->field($model, 'tagNames')->textInput()->hint(Yii::t('cms', 'Separate tags with commas.')) ?>
            </div>
        <?php endif ?>
        <div class="panel">
            <h2><?= Html::encode(Yii::t('cms', 'Featured image')) ?></h2>
            <?php if ($model->featuredMedia): ?>
                <img src="<?= Html::encode($model->featuredMedia->getUrl()) ?>" alt="" style="width:100%;border-radius:8px;margin-bottom:8px;">
            <?php endif ?>
            <?= $form->field($model, 'featured_media_id')->dropDownList($mediaList)->label(Yii::t('cms', 'Library')) ?>
            <label class="form-label"><?= Html::encode(Yii::t('cms', 'Upload')) ?></label>
            <input class="form-control" type="file" name="featured" accept="image/*">
        </div>
    </div>
</div>
<?php ActiveForm::end() ?>
