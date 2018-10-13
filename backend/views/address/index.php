<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\AddressSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $searchModel->userName."'s Addresses";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="address-index">

    <h1><?= $searchModel->userLink ?><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Address', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            //['attribute'=>'userLink', 'format'=>'raw'],
            'receipient_name:ntext',
            'telephone',
            'address',
            'post_code',
            'city',
            'country.name_en',
            //'trade',
            //'tax',
            //'inland_revenue',
            //'created_at',
            //'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
