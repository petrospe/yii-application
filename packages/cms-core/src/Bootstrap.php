<?php

namespace sxoli\cms;

use sxoli\cms\models\Setting;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\helpers\Url;
use yii\web\Application as WebApplication;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Yii::setAlias('@sxoli/cms', dirname(__DIR__));
        Yii::setAlias('@uploads', dirname(__DIR__, 3) . '/frontend/web/uploads');

        if (!$app->has('cms')) {
            $app->set('cms', ['class' => Cms::class]);
        }

        $this->applySiteSettings($app);
    }

    private function applySiteSettings(Application $app): void
    {
        try {
            if ($app->db->getTableSchema(Setting::tableName(), true) === null) {
                return;
            }
            $timezone = Setting::get('timezone');
            if ($timezone) {
                $app->timeZone = $timezone;
                $app->formatter->timeZone = $timezone;
                $app->formatter->defaultTimeZone = $timezone;
            }
            $language = Languages::defaultCode();
            if ($app instanceof WebApplication && $app->id === 'app-frontend') {
                $path = trim((string) $app->request->pathInfo, '/');
                $first = explode('/', $path, 2)[0] ?? '';
                if (Languages::isSupported($first)) {
                    $language = $first;
                }
            } elseif ($app instanceof WebApplication && $app->id === 'app-backend') {
                $chosen = $app->session->get('cms_admin_lang');
                if (Languages::isSupported(is_string($chosen) ? $chosen : null)) {
                    $language = $chosen;
                }
            }
            Languages::apply($app, $language);
            $name = Setting::localized('site_name');
            if ($name !== '') {
                $app->name = $name;
            }
            if ($app instanceof WebApplication && $app->id === 'app-frontend') {
                $app->homeUrl = $language === Languages::defaultCode() ? '/' : '/' . $language;
                $app->on(Module::EVENT_BEFORE_ACTION, static function () use ($app): void {
                    $lang = $app->request->get('lang');
                    if (!is_string($lang) || $lang !== Languages::defaultCode()) {
                        return;
                    }
                    $params = $app->request->getQueryParams();
                    unset($params['lang']);
                    $params[0] = '/' . $app->requestedRoute;
                    $app->response->redirect(Url::to($params), 301);
                    $app->end();
                });
            }
        } catch (\Throwable $e) {
            Yii::warning('CMS settings are not available yet: ' . $e->getMessage(), __METHOD__);
        }
    }
}
