<?php

namespace sxoli\cms\models;

use sxoli\cms\Sanitizer;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $content_id
 * @property int|null $parent_id
 * @property int|null $user_id
 * @property string $author_name
 * @property string $author_email
 * @property string|null $author_url
 * @property string $body
 * @property string $status
 * @property string|null $ip
 * @property string $created_at
 *
 * @property Content $content
 * @property User|null $user
 * @property self|null $parent
 * @property self[] $children
 */
class Comment extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SPAM = 'spam';
    public const STATUS_TRASH = 'trash';

    /** Filled by guests. Real people leave it empty. */
    public string $website_url = '';

    public static function tableName(): string
    {
        return '{{%cms_comment}}';
    }

    public function rules(): array
    {
        return [
            [['content_id', 'author_name', 'author_email', 'body'], 'required'],
            [['content_id', 'parent_id', 'user_id'], 'integer'],
            [['author_name'], 'string', 'max' => 128],
            [['author_email'], 'email'],
            [['author_url'], 'url'],
            [['body'], 'string', 'max' => 5000],
            ['website_url', 'safe'],
            ['status', 'in', 'range' => array_keys(self::statusLabels())],
            ['status', 'default', 'value' => self::STATUS_PENDING],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'author_name' => Yii::t('cms', 'Name'),
            'author_email' => Yii::t('cms', 'Email'),
            'author_url' => Yii::t('cms', 'Website'),
            'body' => Yii::t('cms', 'Comment'),
            'status' => Yii::t('cms', 'Status'),
            'created_at' => Yii::t('cms', 'Date'),
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->body = Sanitizer::comment((string) $this->body);
        if ($insert && !$this->created_at) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        if ($insert && !$this->ip && Yii::$app instanceof \yii\web\Application) {
            $this->ip = Yii::$app->request->userIP;
        }
        return true;
    }

    public function getContent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Content::class, ['id' => 'content_id']);
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getParent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    public function getChildren(): \yii\db\ActiveQuery
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => Yii::t('cms', 'Pending'),
            self::STATUS_APPROVED => Yii::t('cms', 'Approved'),
            self::STATUS_SPAM => Yii::t('cms', 'Spam'),
            self::STATUS_TRASH => Yii::t('cms', 'Trash'),
        ];
    }

    /**
     * @param self[] $comments
     * @return self[]
     */
    public static function tree(array $comments): array
    {
        $grouped = [];
        foreach ($comments as $comment) {
            $grouped[(int) $comment->parent_id][] = $comment;
        }
        $walk = function (int $parentId) use (&$walk, $grouped): array {
            $branch = [];
            foreach ($grouped[$parentId] ?? [] as $comment) {
                $comment->populateRelation('children', $walk((int) $comment->id));
                $branch[] = $comment;
            }
            return $branch;
        };
        return $walk(0);
    }
}
