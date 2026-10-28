# Sxoli CMS

WordPress-style CMS on Yii 2.0.55. The repository is a Composer monorepo: the public site, the admin, and the console stay at the root, and the CMS itself lives in `packages/`.

```
packages/cms-core      models, RBAC, migrations, hooks
packages/cms-admin     administration module
packages/cms-theme     public theme
packages/plugins/*     plugins (hello is the sample)
frontend               public site
backend                administration
console                yii commands
common                 shared configuration
```

## Requirements

- PHP 8.1 or newer, with `pdo_mysql`, `mbstring`, `intl`, `fileinfo`, `json`, `openssl`, `ctype`, and `tokenizer`
- Composer 2
- MySQL 5.7+ or MariaDB, with `utf8mb4`
- A web server that can rewrite requests to `index.php` (Apache `mod_rewrite`, nginx, or the PHP built-in server)

Uploads are limited by the application to 15 MB. Set `upload_max_filesize` and `post_max_size` at least that high.

## Install

From the project root:

```bash
composer install
php init --env=Development --overwrite=n
```

`php init` copies the environment templates into the local config files and fills empty cookie validation keys. Use `--overwrite=n` when those files already exist and you want to keep them. For a production checkout use `--env=Production`. That copies entry scripts with `YII_DEBUG` off and `YII_ENV` set to `prod`, and it does not enable the debug or Gii modules.

Create an empty database, then set the credentials and the public URLs described below. Install the schema, roles, and starter content:

```bash
php yii cms/install
```

The same command is available as `composer cms-install`. It is safe to run again: migrations are applied, roles are refreshed, and settings, starter pages, and the operator account are created only when they are missing. Existing users keep their passwords. A user who does not already have a CMS role is mapped from the legacy `role_id`: `2` or higher becomes `admin`, everyone else becomes `subscriber`. If there is no user named `cms`, one is created and the password is printed once.

Set a password later with:

```bash
php yii cms/password <username> <password>
```

The password must be at least 8 characters.

These directories must be writable by the web server:

```
frontend/runtime
frontend/web/assets
frontend/web/uploads
backend/runtime
backend/web/assets
console/runtime
```

`php init` marks the runtime and asset directories writable. `cms/install` creates `frontend/web/uploads`. PHP execution in that directory is denied by `frontend/web/uploads/.htaccess`.

## Local server

Run both commands from the project root. The router scripts are required because the built-in server does not read `.htaccess`.

```bash
php -S 127.0.0.1:8080 -t frontend/web frontend/web/router.php
php -S 127.0.0.1:8081 -t backend/web backend/web/router.php
```

The public site is then at `http://127.0.0.1:8080` and the admin at `http://127.0.0.1:8081`. Sign in with an administrator account. The admin home is the dashboard.

## Configuration

Local files (`*-local.php`) override the shared config and are the place for secrets. Edit them on each machine. Do not commit real passwords.

### Database and mail

`common/config/main-local.php` is created from `environments/dev/common/config/main-local.php` or the `prod` copy. Point it at the database you created. The charset must stay `utf8mb4`.

```php
'db' => [
    'class' => yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=yii-application',
    'username' => 'db-user',
    'password' => 'db-password',
    'charset' => 'utf8mb4',
],
```

In development the mailer writes messages to `runtime/mail` because `useFileTransport` is `true`. Password reset will not leave the server until that is turned off. For real delivery set a Symfony Mailer DSN in the same file:

```php
'mailer' => [
    'class' => yii\symfonymailer\Mailer::class,
    'viewPath' => '@common/mail',
    'useFileTransport' => false,
    'transport' => [
        'dsn' => 'smtp://user:pass@smtp.example.com:587',
    ],
],
```

The reset message is sent from the **Email** value in admin Settings (`admin_email`). `supportEmail` in `common/config/params.php` is only the fallback. `user.passwordResetTokenExpire` is the token lifetime in seconds (default 3600). `adminEmail` is the address stored as `admin_email` on the first install.

### Cookie validation keys

`frontend/config/main-local.php` and `backend/config/main-local.php` each need their own `cookieValidationKey`. `php init` generates them when the value is empty. Login and cookies fail when a key is empty. Generate a new one with:

```bash
php -r "echo bin2hex(random_bytes(16)), PHP_EOL;"
```

The development copies of these files also enable `yii\debug\Module` and `yii\gii\Module`. Leave them off on a public server. The production templates do not include them. Both modules are `require-dev` packages, so omit them from a production `composer install --no-dev`.

### URLs

Set these in `common/config/params.php` **before the first** `php yii cms/install`. They are copied into the database only when `site_url` and `admin_url` do not exist yet.

```php
'frontendUrl' => 'https://example.com',
'backendUrl' => 'https://admin.example.com',
```

After installation, change **Site URL** and **Admin URL** in the admin Settings screen. Those stored values are what the site uses for permalinks, media URLs, and menu links. The values in `params.php` are not read again for that.

Password-reset links are built from the frontend request host. If the site is behind a reverse proxy and the host PHP sees is wrong, set the frontend request host explicitly in `frontend/config/main-local.php`:

```php
'components' => [
    'request' => [
        'hostInfo' => 'https://example.com',
    ],
],
```

### Web server

The public document root is `frontend/web`. The admin document root is `backend/web`. Do not point a vhost at the repository root.

Apache needs `AllowOverride` so the `.htaccess` files can rewrite every non-file request to `index.php`. A minimal nginx site for the public app:

```nginx
root /var/www/html/yii-application/frontend/web;
index index.php;

location / {
    try_files $uri $uri/ /index.php?$args;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php-fpm.sock;
}
```

Use the same `location` blocks for the admin vhost with `root` set to `backend/web`.

### Site settings

`common/config/main.php` sets the application id, the fallback language (`el`), the source language (`en-US`), and the timezone (`Europe/Athens`). The **Language** value in admin Settings is the public site's default. Visitors switch between Greek and English from the header. The other language is served under a prefix (`/en/...` when Greek is the default, `/el/...` when English is). Unprefixed URLs stay on the default language.

The admin has its own language switch in the top bar. That choice is stored in the session and does not change the public default.

Interface strings are English in the source and Greek in `packages/cms-core/messages/el`. Each post or page has its own content language, plus a translation panel for the other one. Categories, tags, menu labels, widget text, the site name and the tagline are stored in the default language and can be translated on their edit screens. An empty translation falls back to the original text. `php yii cms/install` adds English copy for the starter content.

The Settings screen also controls:

| Setting | Values |
| --- | --- |
| Front page | latest posts, or one published page |
| Posts per page | integer |
| Permalinks | `/sample-post` or `/2026/10/sample-post` |
| Comments | allowed, held for approval, or limited to signed-in users |

### Plugins

A plugin is a directory in `packages/plugins` with its own `composer.json` and a class that implements `sxoli\cms\PluginInterface`. Register it in the root `composer.json` (the path repositories `packages/*` and `packages/plugins/*` are already configured), then run `composer update`.

Activation is the `active_plugins` row in `cms_setting`: a JSON list of class names. The installer enables `sxoli\plugins\hello\Plugin`, which registers the `hello` widget. There is no plugin screen; change that row to add or remove plugins, then reload. The sample value is:

```json
["sxoli\\plugins\\hello\\Plugin"]
```

## Roles

| Role | Access |
| --- | --- |
| admin | settings, users, appearance, all content, comments, media |
| editor | appearance, all content, comments, media |
| author | own posts, publishing, media |
| contributor | own posts, without publishing |
| subscriber | own profile |

Only `admin` can open Settings and manage users. The other roles that include `accessAdmin` can open the admin and are limited by those permissions.
