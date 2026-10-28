<?php

use yii\bootstrap5\Html;

$this->title = Yii::t('cms', 'Install');
?>
<div class="panel">
    <h1><?= Html::encode(Yii::t('cms', 'The CMS is not installed yet')) ?></h1>
    <p><?= Html::encode(Yii::t('cms', 'From the project root, run:')) ?></p>
    <pre>php yii cms/install</pre>
</div>
