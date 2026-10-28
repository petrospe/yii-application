<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var sxoli\cms\models\Content[] $posts */

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
<channel>
    <title><?= Html::encode(Yii::$app->name) ?></title>
    <link><?= Html::encode(Url::home(true)) ?></link>
    <description><?= Html::encode(\sxoli\cms\models\Setting::localized('tagline')) ?></description>
    <?php foreach ($posts as $post): ?>
        <item>
            <title><?= Html::encode($post->text('title')) ?></title>
            <link><?= Html::encode(Url::to($post->getUrl(), true)) ?></link>
            <description><?= Html::encode($post->text('excerpt')) ?></description>
            <pubDate><?= date(DATE_RSS, strtotime((string) $post->published_at)) ?></pubDate>
            <guid><?= Html::encode(Url::to($post->getUrl(), true)) ?></guid>
        </item>
    <?php endforeach ?>
</channel>
</rss>
