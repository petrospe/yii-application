<?php

namespace sxoli\cms\services;

use sxoli\cms\models\User;
use Yii;

class RbacSeeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'accessAdmin' => 'Open the administration area',
        'manageSettings' => 'Change site settings',
        'manageUsers' => 'Manage users',
        'manageAppearance' => 'Manage menus and widgets',
        'editPosts' => 'Edit every post',
        'editOwnPosts' => 'Edit own posts',
        'publishPosts' => 'Publish posts',
        'deletePosts' => 'Delete every post',
        'deleteOwnPosts' => 'Delete own posts',
        'editPages' => 'Edit pages',
        'publishPages' => 'Publish pages',
        'deletePages' => 'Delete pages',
        'moderateComments' => 'Moderate comments',
        'uploadMedia' => 'Upload media',
        'editOwnProfile' => 'Edit own profile',
    ];

    /** @var array<string, string[]> */
    private const ROLES = [
        'admin' => [
            'accessAdmin', 'manageSettings', 'manageUsers', 'manageAppearance',
            'editPosts', 'editOwnPosts', 'publishPosts', 'deletePosts', 'deleteOwnPosts',
            'editPages', 'publishPages', 'deletePages', 'moderateComments', 'uploadMedia', 'editOwnProfile',
        ],
        'editor' => [
            'accessAdmin', 'manageAppearance', 'editPosts', 'editOwnPosts', 'publishPosts',
            'deletePosts', 'deleteOwnPosts', 'editPages', 'publishPages', 'deletePages',
            'moderateComments', 'uploadMedia', 'editOwnProfile',
        ],
        'author' => [
            'accessAdmin', 'editOwnPosts', 'publishPosts', 'deleteOwnPosts', 'uploadMedia', 'editOwnProfile',
        ],
        'contributor' => [
            'accessAdmin', 'editOwnPosts', 'deleteOwnPosts', 'editOwnProfile',
        ],
        'subscriber' => [
            'editOwnProfile',
        ],
    ];

    public function seed(): void
    {
        $auth = Yii::$app->authManager;
        $permissions = [];
        foreach (self::PERMISSIONS as $name => $description) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $description;
                $auth->add($permission);
            }
            $permissions[$name] = $permission;
        }

        foreach (self::ROLES as $name => $children) {
            $role = $auth->getRole($name);
            if ($role === null) {
                $role = $auth->createRole($name);
                $role->description = $name;
                $auth->add($role);
            } else {
                $auth->removeChildren($role);
            }
            foreach ($children as $child) {
                $auth->addChild($role, $permissions[$child]);
            }
        }

        foreach (User::find()->all() as $user) {
            $known = array_intersect($user->getRoleNames(), array_keys(self::ROLES));
            if ($known !== []) {
                continue;
            }
            $roleName = (int) $user->role_id >= 2 ? 'admin' : 'subscriber';
            $auth->assign($auth->getRole($roleName), $user->id);
        }
    }
}
