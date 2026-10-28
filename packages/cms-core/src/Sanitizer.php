<?php

namespace sxoli\cms;

use yii\helpers\HtmlPurifier;

class Sanitizer
{
    public static function rich(string $html): string
    {
        return HtmlPurifier::process($html, [
            'HTML.Allowed' => 'p,br,strong,b,em,i,u,ul,ol,li,a[href|title|target],h2,h3,h4,blockquote,img[src|alt|width|height],pre,code,span,hr,table,thead,tbody,tr,th,td',
            'Attr.AllowedFrameTargets' => ['_blank'],
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true],
            'AutoFormat.RemoveEmpty' => false,
        ]);
    }

    public static function comment(string $html): string
    {
        return HtmlPurifier::process($html, [
            'HTML.Allowed' => 'p,br,strong,em,a[href],code',
            'AutoFormat.AutoParagraph' => true,
        ]);
    }
}
