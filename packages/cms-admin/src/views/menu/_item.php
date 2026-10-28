<?php

use yii\bootstrap5\Html;

/** @var sxoli\cms\models\MenuItem $item */
/** @var int $depth */
?>
<div style="margin-left: <?= (int) $depth * 18 ?>px; padding: 8px 0; border-bottom: 1px solid var(--line);">
    <strong><?= Html::encode($item->label) ?></strong>
    <?php foreach (\sxoli\cms\Languages::CODES as $code): ?>
        <?php if ($code === \sxoli\cms\Languages::defaultCode()) {
            continue;
        } ?>
        <input class="form-control form-control-sm mt-1" form="menu-translations" name="ItemLabel[<?= Html::encode($code) ?>][<?= (int) $item->id ?>]" value="<?= Html::encode(\sxoli\cms\models\Translation::value('menu_item', (int) $item->id, $code, 'title')) ?>" placeholder="<?= Html::encode(\sxoli\cms\Languages::labels()[$code]) ?>">
    <?php endforeach ?>
    <span class="help"><?= Html::encode($item->type) ?></span>
    <?= Html::a('↑', ['move', 'id' => $item->id, 'direction' => 'up'], ['data' => ['method' => 'post']]) ?>
    <?= Html::a('↓', ['move', 'id' => $item->id, 'direction' => 'down'], ['data' => ['method' => 'post']]) ?>
    <?= Html::a(Yii::t('cms', 'Delete'), ['delete-item', 'id' => $item->id], ['data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Remove this item?')]]) ?>
</div>
<?php foreach ($item->childrenCache as $child): ?>
    <?= $this->render('_item', ['item' => $child, 'depth' => $depth + 1]) ?>
<?php endforeach ?>
