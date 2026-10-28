<?php

use yii\helpers\Html;

/** @var string $name */
/** @var string $message */

$this->title = $name;
?>
<div class="layout single">
    <div class="panel">
        <h1><?= Html::encode($name) ?></h1>
        <p><?= nl2br(Html::encode($message)) ?></p>
        <p><?= Html::a(Yii::t('cms', 'Back to the home page'), ['/content/index']) ?></p>
    </div>
</div>
