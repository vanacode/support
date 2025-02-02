<?php

namespace Vanacode\Support\Traits;

trait RouteScopeTrait
{
    /**
     * @template Template
     *
     * @param  array<string, Template>  $options
     * @param  Template  $default
     * @return Template
     */
    public function getRouteScopedOptions(array $options, $default)
    {
        $routeOptions = $options[\Route::currentRouteName()] ?? [];
        if ($routeOptions) {
            return $routeOptions;
        }
        foreach ($options as $pattern => $routeOptions) {
            if (\Route::is($pattern)) {
                return $routeOptions;
            }
        }

        return $default;
    }
}
