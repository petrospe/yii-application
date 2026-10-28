<?php

namespace sxoli\cms;

use sxoli\cms\models\Content;
use Yii;

class ContentPolicy
{
    public static function canCreate(string $type): bool
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return false;
        }
        if ($type === Content::TYPE_PAGE) {
            return $user->can('editPages');
        }
        return $user->can('editPosts') || $user->can('editOwnPosts');
    }

    public static function canUpdate(Content $content): bool
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return false;
        }
        if ($content->type === Content::TYPE_PAGE) {
            return $user->can('editPages');
        }
        if ($user->can('editPosts')) {
            return true;
        }
        return $user->can('editOwnPosts') && (int) $content->author_id === (int) $user->id;
    }

    public static function canDelete(Content $content): bool
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return false;
        }
        if ($content->type === Content::TYPE_PAGE) {
            return $user->can('deletePages');
        }
        if ($user->can('deletePosts')) {
            return true;
        }
        return $user->can('deleteOwnPosts') && (int) $content->author_id === (int) $user->id;
    }

    public static function canPublish(Content $content): bool
    {
        if ($content->type === Content::TYPE_PAGE) {
            return Yii::$app->user->can('publishPages');
        }
        return Yii::$app->user->can('publishPosts');
    }

    /** @return array<string, string> */
    public static function statusesFor(Content $content): array
    {
        $all = Content::statusLabels();
        unset($all[Content::STATUS_TRASH]);
        if (!self::canPublish($content)) {
            unset($all[Content::STATUS_PUBLISH], $all[Content::STATUS_PRIVATE]);
        }
        return $all;
    }
}
