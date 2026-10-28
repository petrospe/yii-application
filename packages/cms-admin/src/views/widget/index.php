<?php

use sxoli\cms\models\Widget;
use yii\bootstrap5\Html;

/** @var Widget[] $widgets */

$this->title = Yii::t('cms', 'Widgets');
?>
<div class="page-head">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::a(Yii::t('cms', 'Add new'), ['create'], ['class' => 'btn btn-accent']) ?>
</div>
<div class="panel">
    <table class="cms-table">
        <?php foreach ($widgets as $widget): ?>
            <tr>
                <td><?= Html::a(Html::encode($widget->title ?: $widget->type), ['update', 'id' => $widget->id]) ?></td>
                <td><?= Html::encode(Yii::$app->cms->widgetType($widget->type)['label'] ?? $widget->type) ?></td>
                <td><?= Html::encode(Widget::sidebars()[$widget->sidebar] ?? $widget->sidebar) ?></td>
                <td><?= $widget->enabled ? Html::encode(Yii::t('cms', 'Enabled')) : Html::encode(Yii::t('cms', 'Disabled')) ?></td>
                <td><?= Html::a(Yii::t('cms', 'Delete'), ['delete', 'id' => $widget->id], ['data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Delete this widget?')]]) ?></td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
