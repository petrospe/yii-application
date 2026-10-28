<?php

namespace sxoli\cms\services;

use sxoli\cms\models\Media;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class MediaStorage
{
    private const ALLOWED = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'mp3' => ['audio/mpeg', 'audio/mp3'],
        'mp4' => ['video/mp4'],
        'webm' => ['video/webm'],
        'txt' => ['text/plain'],
    ];

    public static function store(UploadedFile $file, ?int $authorId, ?string $title = null): Media
    {
        $extension = strtolower((string) $file->extension);
        if (!isset(self::ALLOWED[$extension])) {
            throw new \InvalidArgumentException(Yii::t('cms', 'This file type is not allowed.'));
        }
        if ($file->size > 15 * 1024 * 1024) {
            throw new \InvalidArgumentException(Yii::t('cms', 'The file is larger than 15 MB.'));
        }
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->tempName);
        if (!in_array($mime, self::ALLOWED[$extension], true)) {
            throw new \InvalidArgumentException(Yii::t('cms', 'The file contents do not match its extension.'));
        }

        $relativeDir = date('Y/m');
        $directory = Yii::getAlias('@uploads/' . $relativeDir);
        FileHelper::createDirectory($directory);
        $filename = bin2hex(random_bytes(8)) . '.' . $extension;
        $relative = $relativeDir . '/' . $filename;
        if (!$file->saveAs($directory . '/' . $filename)) {
            throw new \RuntimeException(Yii::t('cms', 'The file could not be saved.'));
        }

        $media = new Media();
        $media->title = $title ?: pathinfo($file->name, PATHINFO_FILENAME);
        $media->path = $relative;
        $media->mime = $mime;
        $media->size = (int) $file->size;
        $media->author_id = $authorId;
        $media->created_at = date('Y-m-d H:i:s');
        if (str_starts_with($mime, 'image/') && ($info = @getimagesize($directory . '/' . $filename))) {
            $media->width = (int) $info[0];
            $media->height = (int) $info[1];
        }
        $media->save(false);
        return $media;
    }
}
