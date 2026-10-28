<?php

use sxoli\cms\admin\assets\AdminAsset;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var string $content */

AdminAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title ?: Yii::t('cms', 'Sign in')) ?></title>
    <?php $this->registerCsrfMetaTags() ?>
    <link href="https://fonts.googleapis.com/css2?family=Literata:opsz,wght@7..72,650&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <?php $this->head() ?>
</head>
<body class="auth-body">
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
