<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Entities */

$this->title = Yii::t('app', 'Create Entities');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Entities'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="entities-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
