<?php

use sxoli\cms\Languages;
use sxoli\cms\models\Translation;
use yii\bootstrap5\Html;

/** @var string $entity */
/** @var int $id */
/** @var string $sourceLanguage */
/** @var array<string, string> $fields */
/** @var array<string, string> $multiline */

$posted = Yii::$app->request->post('Translation', []);
$multiline = $multiline ?? [];
?>
<?php foreach (Languages::CODES as $code): ?>
    <?php if ($code === $sourceLanguage) {
        continue;
    } ?>
    <div class="panel">
        <h2><?= Html::encode(Yii::t('cms', 'Translation')) ?> · <?= Html::encode(Languages::labels()[$code]) ?></h2>
        <p class="help"><?= Html::encode(Yii::t('cms', 'Leave a translation empty to show the original text.')) ?></p>
        <?php foreach ($fields as $field => $caption): ?>
            <?php
            $value = $posted[$code][$field] ?? ($id > 0 ? Translation::value($entity, $id, $code, $field) : '');
            $inputId = 'tr-' . $code . '-' . $field;
            $name = 'Translation[' . $code . '][' . $field . ']';
            ?>
            <div class="mb-3">
                <label class="form-label" for="<?= Html::encode($inputId) ?>"><?= Html::encode($caption) ?></label>
                <?php if (in_array($field, $multiline, true)): ?>
                    <textarea class="form-control<?= $field === 'body' ? ' cms-editor' : '' ?>" id="<?= Html::encode($inputId) ?>" name="<?= Html::encode($name) ?>" rows="8"><?= Html::encode($value) ?></textarea>
                <?php else: ?>
                    <input class="form-control" id="<?= Html::encode($inputId) ?>" name="<?= Html::encode($name) ?>" value="<?= Html::encode($value) ?>">
                <?php endif ?>
            </div>
        <?php endforeach ?>
    </div>
<?php endforeach ?>
