<?php

namespace sxoli\cms\widgets;

use sxoli\cms\Sanitizer;

class TextWidget extends CmsWidget
{
    public function run(): string
    {
        if (trim($this->body) === '') {
            return '';
        }
        return $this->wrap('<div class="widget-text">' . Sanitizer::rich($this->body) . '</div>');
    }
}
