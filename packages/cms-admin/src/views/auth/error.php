<?php

use yii\bootstrap5\Html;

/** @var string $name */
/** @var string $message */

$this->title = $name;
?>
<div class="auth-card card">
    <h1><?= Html::encode($name) ?></h1>
    <p><?= nl2br(Html::encode($message)) ?></p>
    <p><a href="<?= \yii\helpers\Url::to(['/cms/auth/login']) ?>"><?= Html::encode(Yii::t('cms', 'Sign in')) ?></a></p>
</div>
