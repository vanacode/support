<?php

namespace Vanacode\Support\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait MethodMatchTrait
{
    private static array $cachedMethodCalls = [];

    protected array $excludeDynamicMethods = [
        //        'prefix' => [
        //            'method1',
        //            'method2',
        //        ],
    ];

    protected array $callFirstDynamicMethods = [
        //        'prefix' => [
        //            'methodcallfirst',
        //            'methodcallsecond',
        //        ],
    ];

    public function getMethodsMatch(string $prefix, string $suffix = '', array|string $exclude = [], array|string $callFirst = []): array
    {
        $methods = [];
        foreach (get_class_methods($this) as $method) {
            if ($prefix && $suffix) {
                if (Str::startsWith($method, $prefix) && Str::endsWith($method, $suffix)) {
                    $methods[] = $method;
                }

                continue;
            }
            if ($prefix && Str::startsWith($method, $prefix)) {
                $methods[] = $method;
            }
            if ($suffix && Str::endsWith($method, $suffix)) {
                $methods[] = $method;
            }
        }
        $methods = array_unique($methods);
        $exclude = $exclude ?: ($this->excludeDynamicMethods[$prefix] ?? []);
        $callFirst = $callFirst ?: ($this->callFirstDynamicMethods[$prefix] ?? []);
        $methods = array_diff($methods, Arr::wrap($exclude));

        return collect($methods)->sortByValues(Arr::wrap($callFirst))->all();
    }

    public function getTraitMatchMethods(string $prefix, string $suffix = ''): array
    {
        $cacheKey = implode('__', [$prefix, $suffix]);
        if (isset(self::$cachedMethodCalls[static::class][$cacheKey])) {
            return self::$cachedMethodCalls[static::class][$cacheKey];
        }
        $methods = [];
        $traits = class_uses_recursive(static::class);
        foreach ($traits as $trait) {
            if (method_exists($this, $method = $prefix.class_basename($trait).$suffix)) {
                $methods[] = $method;
            }
        }

        self::$cachedMethodCalls[static::class][$cacheKey] = $methods;

        return $methods;
    }
}
