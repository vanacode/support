<?php

namespace Vanacode\Support\Traits;

trait InitializeTraits
{
    public function initializeTraits():void
    {
        $class = static::class;
        foreach (class_uses_recursive($class) as $trait) {
            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                $this->{$method}();
            }
        }
    }
}
