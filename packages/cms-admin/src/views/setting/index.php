<?php

use yii\bootstrap5\Html;

/** @var array<string, string> $values */
/** @var sxoli\cms\models\Content[] $pages */

$this->title = Yii::t('cms', 'Settings');
$field = static function (string $name, string $label, string $type = 'text') use ($values) {
    $value = Html::encode($values[$name] ?? '');
    return '<div class="mb-3"><label class="form-label" for="s-' . $name . '">' . Html::encode($label) . '</label><input class="form-control" id="s-' . $name . '" type="' . $type . '" name="Setting[' . $name . ']" value="' . $value . '"></div>';
};
$pageOptions = '<option value="">' . Html::encode(Yii::t('cms', 'None')) . '</option>';
foreach ($pages as $page) {
    $selected = (string) $page->id === ($values['page_on_front'] ?? '') ? ' selected' : '';
    $pageOptions .= '<option value="' . (int) $page->id . '"' . $selected . '>' . Html::encode($page->title) . '</option>';
}
?>
<div class="page-head"><h1><?= Html::encode($this->title) ?></h1></div>
<form method="post">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
    <div class="layout-form">
        <div class="panel">
            <h2><?= Html::encode(Yii::t('cms', 'General')) ?></h2>
            <?php $names = \sxoli\cms\models\Setting::texts('site_name'); ?>
            <?php $taglines = \sxoli\cms\models\Setting::texts('tagline'); ?>
            <?php foreach (\sxoli\cms\Languages::labels() as $code => $language): ?>
                <div class="mb-3">
                    <label class="form-label" for="name-<?= $code ?>"><?= Html::encode(Yii::t('cms', 'Site name')) ?> · <?= Html::encode($language) ?></label>
                    <input class="form-control" id="name-<?= $code ?>" name="SettingText[<?= $code ?>][site_name]" value="<?= Html::encode($names[$code] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="tagline-<?= $code ?>"><?= Html::encode(Yii::t('cms', 'Tagline')) ?> · <?= Html::encode($language) ?></label>
                    <input class="form-control" id="tagline-<?= $code ?>" name="SettingText[<?= $code ?>][tagline]" value="<?= Html::encode($taglines[$code] ?? '') ?>">
                </div>
            <?php endforeach ?>
            <?= $field('site_url', Yii::t('cms', 'Site URL'), 'url') ?>
            <?= $field('admin_url', Yii::t('cms', 'Admin URL'), 'url') ?>
            <?= $field('admin_email', Yii::t('cms', 'Email'), 'email') ?>
            <div class="mb-3">
                <label class="form-label"><?= Html::encode(Yii::t('cms', 'Language')) ?></label>
                <select class="form-select" name="Setting[language]">
                    <option value="el" <?= $values['language'] === 'el' ? 'selected' : '' ?>>Ελληνικά</option>
                    <option value="en" <?= $values['language'] === 'en' ? 'selected' : '' ?>>English</option>
                </select>
            </div>
            <?= $field('timezone', Yii::t('cms', 'Timezone')) ?>
        </div>
        <div>
            <div class="panel">
                <h2><?= Html::encode(Yii::t('cms', 'Reading')) ?></h2>
                <?= $field('posts_per_page', Yii::t('cms', 'Posts per page'), 'number') ?>
                <div class="mb-3">
                    <label class="form-label"><?= Html::encode(Yii::t('cms', 'Front page shows')) ?></label>
                    <select class="form-select" name="Setting[show_on_front]">
                        <option value="posts" <?= $values['show_on_front'] === 'posts' ? 'selected' : '' ?>><?= Html::encode(Yii::t('cms', 'Latest posts')) ?></option>
                        <option value="page" <?= $values['show_on_front'] === 'page' ? 'selected' : '' ?>><?= Html::encode(Yii::t('cms', 'A static page')) ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= Html::encode(Yii::t('cms', 'Front page')) ?></label>
                    <select class="form-select" name="Setting[page_on_front]"><?= $pageOptions ?></select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= Html::encode(Yii::t('cms', 'Permalinks')) ?></label>
                    <select class="form-select" name="Setting[permalink_structure]">
                        <option value="postname" <?= $values['permalink_structure'] === 'postname' ? 'selected' : '' ?>>/sample-post</option>
                        <option value="dated" <?= $values['permalink_structure'] === 'dated' ? 'selected' : '' ?>>/2026/10/sample-post</option>
                    </select>
                </div>
            </div>
            <div class="panel">
                <h2><?= Html::encode(Yii::t('cms', 'Discussion')) ?></h2>
                <?php foreach ([
                    'comments_enabled' => Yii::t('cms', 'Allow comments'),
                    'comment_moderation' => Yii::t('cms', 'Comments must be approved'),
                    'comment_registration' => Yii::t('cms', 'Only signed-in users can comment'),
                ] as $name => $label): ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="Setting[<?= $name ?>]" value="1" id="c-<?= $name ?>" <?= ($values[$name] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="c-<?= $name ?>"><?= Html::encode($label) ?></label>
                    </div>
                <?php endforeach ?>
            </div>
            <button class="btn btn-accent" type="submit"><?= Html::encode(Yii::t('cms', 'Save')) ?></button>
        </div>
    </div>
</form>
