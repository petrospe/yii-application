<?php

namespace sxoli\cms\admin\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

class AdminController extends Controller
{
    public $layout = 'main';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['accessAdmin'],
                    ],
                ],
            ],
        ];
    }
}
