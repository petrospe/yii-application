<?php

namespace sxoli\cms;

interface PluginInterface
{
    public function id(): string;

    public function bootstrap(Cms $cms): void;
}
