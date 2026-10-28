<?php

use sxoli\cms\admin\assets\AdminAsset;
use sxoli\cms\Languages;
use sxoli\cms\models\Comment;
use sxoli\cms\models\Setting;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $content */

AdminAsset::register($this);
$this->registerJs(
    'window.cmsUploadUrl=' . json_encode(Url::to(['/cms/media/upload']))
    . ';window.cmsCsrfParam=' . json_encode(Yii::$app->request->csrfParam)
    . ';window.cmsCsrfToken=' . json_encode(Yii::$app->request->csrfToken) . ';',
    \yii\web\View::POS_HEAD
);

$controller = Yii::$app->controller->id;
$type = (string) Yii::$app->request->get('type', '');
$taxonomy = (string) Yii::$app->request->get('taxonomy', '');
$pending = 0;
try {
    $pending = (int) Comment::find()->where(['status' => Comment::STATUS_PENDING])->count();
} catch (\Throwable $e) {
}
$item = static function (string $label, array $route, bool $active, ?int $badge = null) {
    $html = Html::encode($label);
    if ($badge) {
        $html .= Html::tag('span', (string) $badge, ['class' => 'badge']);
    }
    return Html::a($html, $route, ['class' => $active ? 'active' : null]);
};
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= Html::encode($this->title ? $this->title . ' · ' . Yii::$app->name : Yii::$app->name) ?></title>
    <?php $this->registerCsrfMetaTags() ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Literata:opsz,wght@7..72,560;7..72,700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <?php $this->head() ?>
</head>
<body class="cms-admin">
<?php $this->beginBody() ?>
<div class="cms-shell">
    <aside class="cms-sidebar">
        <a class="cms-brand" href="<?= Url::to(['/cms/dashboard/index']) ?>">SXOLI<small><?= Html::encode(Yii::t('cms', 'Administration')) ?></small></a>
        <?= $item(Yii::t('cms', 'Dashboard'), ['/cms/dashboard/index'], $controller === 'dashboard') ?>
        <?= $item(Yii::t('cms', 'Posts'), ['/cms/content/index', 'type' => 'post'], $controller === 'content' && $type !== 'page') ?>
        <?php if (Yii::$app->user->can('editPages')): ?>
            <?= $item(Yii::t('cms', 'Pages'), ['/cms/content/index', 'type' => 'page'], $controller === 'content' && $type === 'page') ?>
        <?php endif ?>
        <?= $item(Yii::t('cms', 'Media'), ['/cms/media/index'], $controller === 'media') ?>
        <?php if (Yii::$app->user->can('moderateComments')): ?>
            <?= $item(Yii::t('cms', 'Comments'), ['/cms/comment/index'], $controller === 'comment', $pending) ?>
        <?php endif ?>
        <?php if (Yii::$app->user->can('editPosts') || Yii::$app->user->can('editPages')): ?>
            <div class="group"><?= Html::encode(Yii::t('cms', 'Organisation')) ?></div>
            <?= $item(Yii::t('cms', 'Categories'), ['/cms/term/index', 'taxonomy' => 'category'], $controller === 'term' && $taxonomy !== 'post_tag') ?>
            <?= $item(Yii::t('cms', 'Tags'), ['/cms/term/index', 'taxonomy' => 'post_tag'], $controller === 'term' && $taxonomy === 'post_tag') ?>
        <?php endif ?>
        <?php if (Yii::$app->user->can('manageAppearance')): ?>
            <div class="group"><?= Html::encode(Yii::t('cms', 'Appearance')) ?></div>
            <?= $item(Yii::t('cms', 'Menus'), ['/cms/menu/index'], $controller === 'menu') ?>
            <?= $item(Yii::t('cms', 'Widgets'), ['/cms/widget/index'], $controller === 'widget') ?>
        <?php endif ?>
        <?php if (Yii::$app->user->can('manageUsers')): ?>
            <?= $item(Yii::t('cms', 'Users'), ['/cms/user/index'], $controller === 'user') ?>
        <?php endif ?>
        <?php if (Yii::$app->user->can('manageSettings')): ?>
            <?= $item(Yii::t('cms', 'Settings'), ['/cms/setting/index'], $controller === 'setting') ?>
        <?php endif ?>
    </aside>
    <div class="cms-main">
        <header class="cms-topbar">
            <div><?= Html::encode(Yii::$app->user->identity->displayLabel ?? '') ?></div>
            <div>
                <span class="lang-switch">
                    <?php foreach (Languages::labels() as $code => $label): ?>
                        <?= Html::a($label, ['/cms/language/switch', 'lang' => $code], [
                            'class' => Yii::$app->language === $code ? 'active' : null,
                        ]) ?>
                    <?php endforeach ?>
                </span>
                <?php
                $siteUrl = rtrim((string) Setting::get('site_url', Yii::$app->params['frontendUrl'] ?? '/'), '/');
                if (Yii::$app->language !== Languages::defaultCode()) {
                    $siteUrl .= '/' . Yii::$app->language;
                }
                ?>
                <a href="<?= Html::encode($siteUrl) ?>" target="_blank" rel="noopener"><?= Html::encode(Yii::t('cms', 'View site')) ?></a>
                ·
                <?= Html::a(Yii::t('cms', 'Log out'), ['/cms/auth/logout'], ['data' => ['method' => 'post']]) ?>
            </div>
        </header>
        <div class="cms-content">
            <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?>"><?= Html::encode(is_array($message) ? implode(' ', $message) : $message) ?></div>
            <?php endforeach ?>
            <?= $content ?>
        </div>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
