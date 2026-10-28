<?php

use sxoli\cms\Languages;
use sxoli\cms\models\Menu;
use sxoli\cms\models\Setting;
use sxoli\cms\services\WidgetRenderer;
use sxoli\cms\widgets\MenuWidget;
use sxoli\theme\assets\ThemeAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $content */

ThemeAsset::register($this);
$description = trim(strip_tags((string) ($this->params['description'] ?? Setting::get('tagline', ''))));
if ($description !== '') {
    $this->registerMetaTag(['name' => 'description', 'content' => $description], 'description');
}
$primary = Menu::findByLocation(Menu::LOCATION_PRIMARY);
$footer = Menu::findByLocation(Menu::LOCATION_FOOTER);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title ? $this->title . ' · ' . Yii::$app->name : Yii::$app->name) ?></title>
    <link rel="alternate" type="application/rss+xml" title="<?= Html::encode(Yii::$app->name) ?>" href="<?= Html::encode(Url::to(['/content/feed'], true)) ?>">
    <?php foreach (Languages::CODES as $code): ?>
        <?php $alternate = $this->params['languageUrls'][$code] ?? Url::current(['lang' => $code]); ?>
        <link rel="alternate" hreflang="<?= Html::encode($code) ?>" href="<?= Html::encode(Url::to($alternate, true)) ?>">
    <?php endforeach ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Literata:opsz,wght@7..72,560;7..72,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head() ?>
</head>
<body class="site">
<?php $this->beginBody() ?>
<header class="site-header">
    <div class="wrap header-inner">
        <a class="brand" href="<?= Url::home() ?>">
            <strong><?= Html::encode(Yii::$app->name) ?></strong>
            <span><?= Html::encode(Setting::localized('tagline')) ?></span>
        </a>
        <?php if ($primary): ?>
            <?= MenuWidget::renderItems($primary->tree(), 'nav') ?>
        <?php endif ?>
        <nav class="langs" aria-label="<?= Html::encode(Yii::t('cms', 'Language')) ?>">
            <?php foreach (Languages::labels() as $code => $label): ?>
                <?php $href = $this->params['languageUrls'][$code] ?? Url::current(['lang' => $code]); ?>
                <?= Html::a($label, $href, [
                    'class' => Yii::$app->language === $code ? 'active' : null,
                    'hreflang' => $code,
                    'lang' => $code,
                ]) ?>
            <?php endforeach ?>
        </nav>
        <nav class="account">
            <?php if (Yii::$app->user->isGuest): ?>
                <?= Html::a(Yii::t('cms', 'Sign in'), ['/site/login']) ?>
            <?php else: ?>
                <?= Html::a(Yii::t('cms', 'Profile'), ['/profile/index']) ?>
                <?php if (Yii::$app->user->can('accessAdmin')): ?>
                    · <a href="<?= Html::encode((string) Setting::get('admin_url', Yii::$app->params['backendUrl'] ?? '/')) ?>"><?= Html::encode(Yii::t('cms', 'Administration')) ?></a>
                <?php endif ?>
                · <?= Html::a(Yii::t('cms', 'Log out'), ['/site/logout'], ['data' => ['method' => 'post']]) ?>
            <?php endif ?>
        </nav>
    </div>
</header>
<main class="wrap">
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?>"><?= Html::encode(is_array($message) ? implode(' ', $message) : $message) ?></div>
    <?php endforeach ?>
    <?= $content ?>
</main>
<footer class="site-footer">
    <div class="wrap footer-grid">
        <div>
            <?php if ($footer): ?>
                <?= MenuWidget::renderItems($footer->tree(), 'footer-menu') ?>
            <?php endif ?>
            <p class="meta">© <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?></p>
        </div>
        <div><?= WidgetRenderer::renderSidebar('footer') ?></div>
    </div>
</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
