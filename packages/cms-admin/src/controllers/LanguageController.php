<?php

namespace sxoli\cms\admin\controllers;

use sxoli\cms\Languages;
use Yii;
use yii\web\BadRequestHttpException;

class LanguageController extends AdminController
{
    public function actionSwitch(string $lang)
    {
        if (!Languages::isSupported($lang)) {
            throw new BadRequestHttpException();
        }
        Yii::$app->session->set('cms_admin_lang', $lang);
        return $this->redirect(Yii::$app->request->referrer ?: ['/cms/dashboard/index']);
    }
}
