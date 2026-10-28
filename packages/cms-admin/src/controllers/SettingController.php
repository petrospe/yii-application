<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\Languages;
use sxoli\cms\models\Content;
use sxoli\cms\models\Setting;
use sxoli\cms\models\Translation;
use Yii;
use yii\web\ForbiddenHttpException;

class SettingController extends AdminController
{
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->can('manageSettings')) {
            throw new ForbiddenHttpException();
        }
        return true;
    }

    public function actionIndex()
    {
        $keys = [
            'site_name', 'tagline', 'site_url', 'admin_url', 'admin_email', 'language', 'timezone',
            'posts_per_page', 'show_on_front', 'page_on_front', 'comments_enabled', 'comment_moderation',
            'comment_registration', 'permalink_structure',
        ];
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = (string) Setting::get($key, '');
        }
        if (Yii::$app->request->isPost) {
            $posted = Yii::$app->request->post('Setting', []);
            $save = [];
            foreach ($keys as $key) {
                if (in_array($key, ['comments_enabled', 'comment_moderation', 'comment_registration'], true)) {
                    $save[$key] = empty($posted[$key]) ? '0' : '1';
                } else {
                    $save[$key] = trim((string) ($posted[$key] ?? ''));
                }
            }
            $texts = Yii::$app->request->post('SettingText', []);
            $default = Languages::isSupported($save['language']) ? $save['language'] : Languages::defaultCode();
            if (is_array($texts)) {
                foreach (['site_name', 'tagline'] as $name) {
                    $save[$name] = trim((string) ($texts[$default][$name] ?? $save[$name] ?? ''));
                }
            }
            Setting::setMany($save);
            if (is_array($texts)) {
                foreach (Languages::CODES as $code) {
                    if ($code === $default) {
                        continue;
                    }
                    foreach (['site_name', 'tagline'] as $name) {
                        $row = Setting::findOne(['name' => $name]);
                        if ($row === null) {
                            continue;
                        }
                        Translation::store('setting', (int) $row->id, $code, [
                            'title' => trim((string) ($texts[$code][$name] ?? '')),
                        ]);
                    }
                }
            }
            if (Yii::$app->language === $default) {
                Yii::$app->name = $save['site_name'] ?: Yii::$app->name;
            }
            Yii::$app->session->setFlash('success', Yii::t('cms', 'Settings saved.'));
            return $this->refresh();
        }
        $pages = Content::find()
            ->where(['type' => Content::TYPE_PAGE, 'status' => Content::STATUS_PUBLISH])
            ->orderBy(['title' => SORT_ASC])
            ->all();
        return $this->render('index', ['values' => $values, 'pages' => $pages]);
    }
}
