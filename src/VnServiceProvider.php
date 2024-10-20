<?php

namespace Vanacode\Support;

class VnServiceProvider extends CoreServiceProvider
{
    public string $packagePrefix = 'vn_';

    protected function detectPackageName(string $callPath): string
    {
        return $this->packagePrefix.parent::detectPackageName($callPath);
    }
}
