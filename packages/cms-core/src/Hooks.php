<?php

namespace sxoli\cms;

/**
 * Small WordPress-style filter bus. Plugins call on() during bootstrap
 * and the theme calls apply() when rendering content.
 */
class Hooks
{
    /** @var array<string, array<int, callable[]>> */
    private array $listeners = [];

    public function on(string $hook, callable $listener, int $priority = 10): void
    {
        $this->listeners[$hook][$priority][] = $listener;
    }

    public function apply(string $hook, mixed $value, array $context = []): mixed
    {
        if (empty($this->listeners[$hook])) {
            return $value;
        }
        ksort($this->listeners[$hook]);
        foreach ($this->listeners[$hook] as $group) {
            foreach ($group as $listener) {
                $value = $listener($value, $context);
            }
        }
        return $value;
    }
}
