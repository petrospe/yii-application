<?php

use yii\bootstrap5\Html;

/** @var sxoli\cms\models\Menu[] $menus */

$this->title = Yii::t('cms', 'Menus');
?>
<div class="page-head">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::a(Yii::t('cms', 'Add new'), ['create'], ['class' => 'btn btn-accent']) ?>
</div>
<div class="panel">
    <table class="cms-table">
        <?php foreach ($menus as $menu): ?>
            <tr>
                <td><?= Html::a(Html::encode($menu->name), ['update', 'id' => $menu->id]) ?></td>
                <td><?= Html::encode($menu->location ? (\sxoli\cms\models\Menu::locations()[$menu->location] ?? $menu->location) : '—') ?></td>
                <td><?= count($menu->items) ?></td>
                <td><?= Html::a(Yii::t('cms', 'Delete'), ['delete', 'id' => $menu->id], ['data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Delete this menu?')]]) ?></td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
