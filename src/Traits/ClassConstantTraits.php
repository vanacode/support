<?php

namespace Vanacode\Support\Traits;

use Illuminate\Support\Str;

trait ClassConstantTraits
{
    public static function getConstantsByPrefix(string $prefix, bool $strToLower = true, array $exclude = [], bool $cutPrefix = true): array
    {
        $allConstants = self::getClassConstants();
        $constants = [];
        foreach ($allConstants as $constant => $value) {
            if (Str::startsWith($constant, $prefix) && ! in_array($constant, $exclude)) {
                if ($cutPrefix) {
                    $constant = Str::replaceFirst($prefix, '', $constant);
                    $constant = trim($constant, '_');
                }
                if ($strToLower) {
                    $constant = strtolower($constant);
                }
                $constants[$constant] = $value;
            }
        }

        return $constants;
    }

    public static function getClassConstants(): array
    {

        $class = new \ReflectionClass(static::class);

        return $class->getConstants();
    }
}
