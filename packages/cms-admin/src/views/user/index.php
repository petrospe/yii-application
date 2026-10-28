<?php

use sxoli\cms\models\User;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;

/** @var yii\data\ActiveDataProvider $provider */

$this->title = Yii::t('cms', 'Users');
?>
<div class="page-head">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::a(Yii::t('cms', 'Add new'), ['create'], ['class' => 'btn btn-accent']) ?>
</div>
<div class="panel">
    <table class="cms-table">
        <thead>
        <tr>
            <th><?= Html::encode(Yii::t('cms', 'Username')) ?></th>
            <th><?= Html::encode(Yii::t('cms', 'Email')) ?></th>
            <th><?= Html::encode(Yii::t('cms', 'Role')) ?></th>
            <th><?= Html::encode(Yii::t('cms', 'Status')) ?></th>
            <th></th>
        </tr>
        </thead>
        <?php foreach ($provider->getModels() as $user): ?>
            <tr>
                <td><?= Html::a(Html::encode($user->displayLabel), ['update', 'id' => $user->id]) ?></td>
                <td><?= Html::encode($user->email) ?></td>
                <td><?= Html::encode(implode(', ', $user->getRoleNames())) ?></td>
                <td><?= Html::encode(User::statusList()[$user->status_id] ?? (string) $user->status_id) ?></td>
                <td>
                    <?php if ((int) $user->id !== (int) Yii::$app->user->id): ?>
                        <?= Html::a(Yii::t('cms', 'Delete'), ['delete', 'id' => $user->id], ['data' => ['method' => 'post', 'confirm' => Yii::t('cms', 'Delete this user?')]]) ?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
<?= LinkPager::widget(['pagination' => $provider->pagination]) ?>
