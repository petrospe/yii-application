<?php

use sxoli\cms\models\Content;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $provider */
/** @var string $type */
/** @var string|null $status */

$label = $type === Content::TYPE_PAGE ? Yii::t('cms', 'Pages') : Yii::t('cms', 'Posts');
$this->title = $label;
$tabs = [
    '' => Yii::t('cms', 'All'),
    Content::STATUS_PUBLISH => Yii::t('cms', 'Published'),
    Content::STATUS_DRAFT => Yii::t('cms', 'Draft'),
    Content::STATUS_PENDING => Yii::t('cms', 'Pending review'),
    Content::STATUS_TRASH => Yii::t('cms', 'Trash'),
];
?>
<div class="page-head">
    <h1><?= Html::encode($label) ?></h1>
    <?= Html::a(Yii::t('cms', 'Add new'), ['create', 'type' => $type], ['class' => 'btn btn-accent']) ?>
</div>
<div class="filters">
    <?php foreach ($tabs as $value => $caption): ?>
        <?= Html::a(Html::encode($caption), ['index', 'type' => $type, 'status' => $value ?: null], ['class' => (string) $status === (string) $value ? 'active' : null]) ?>
    <?php endforeach ?>
</div>
<div class="panel">
    <table class="cms-table">
        <thead>
        <tr>
            <th><?= Html::encode(Yii::t('cms', 'Title')) ?></th>
            <th><?= Html::encode(Yii::t('cms', 'Author')) ?></th>
            <th><?= Html::encode(Yii::t('cms', 'Status')) ?></th>
            <th><?= Html::encode(Yii::t('cms', 'Updated')) ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($provider->getModels() as $model): ?>
            <tr>
                <td>
                    <?= Html::a(Html::encode($model->title), ['update', 'id' => $model->id]) ?>
                    <span class="help"><?= Html::encode(strtoupper((string) $model->language)) ?></span>
                    <?php if ($model->status === Content::STATUS_PUBLISH): ?>
                        <div class="help"><a href="<?= Html::encode($model->getUrl()) ?>" target="_blank" rel="noopener"><?= Html::encode(Yii::t('cms', 'View')) ?></a></div>
                    <?php endif ?>
                </td>
                <td><?= Html::encode($model->author->displayLabel ?? '') ?></td>
                <td><span class="status <?= Html::encode($model->status) ?>"><?= Html::encode(Content::statusLabels()[$model->status] ?? $model->status) ?></span></td>
                <td><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></td>
                <td>
                    <?php if ($model->status === Content::STATUS_TRASH): ?>
                        <?= Html::a(Yii::t('cms', 'Delete permanently'), ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Delete this permanently?')],
                        ]) ?>
                    <?php else: ?>
                        <?= Html::a(Yii::t('cms', 'Trash'), ['trash', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Move this item to the trash?')],
                        ]) ?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
    <?php if ($provider->getCount() === 0): ?>
        <p class="help mb-0"><?= Html::encode(Yii::t('cms', 'Nothing here yet.')) ?></p>
    <?php endif ?>
</div>
<?= LinkPager::widget(['pagination' => $provider->pagination]) ?>
