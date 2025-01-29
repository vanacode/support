<?php

namespace Vanacode\Support;

class VnServiceProvider extends CoreServiceProvider
{
    public string $packagePrefix = 'vn_';

    /**
     * Always give prefix for config or view share cases
     */
    protected function detectPackageName(string $callPath): string
    {
        return $this->packagePrefix.parent::detectPackageName($callPath);
    }
}
