<?php

namespace Vanacode\Support\Traits;

use Illuminate\Support\Str;

trait ClassDetailsTrait
{
    /**
     * class root namespace
     *
     * detect based parent class namespace,
     * assuming parent and child classes has same root namespace
     */
    protected string $classRootNamespace;

    /**
     * class suffix
     *
     * detect based class root namespace, assuming as singular
     */
    protected string $classSuffix;

    /**
     * class basename without suffix
     */
    protected string $classNameWithoutSuffix;

    /**
     * Class sub folders after root folder
     *
     * detect based class root namespace and basename
     */
    protected array $classSubFolders;

    /**
     * detect application(package) root namespace and concat relative namespace
     */
    protected function getTargetRootNamespace(string $relativeNamespace): string
    {
        $rootNamespace = Str::before(static::class, '\\').'\\';

        return $relativeNamespace ? $rootNamespace.$relativeNamespace.'\\' : $rootNamespace;
    }

    /**
     * Get class sub folders after class root folder
     *
     * detect based class root namespace and basename
     */
    protected function getClassSubFolders(): array
    {
        if (! isset($this->classSubFolders)) {
            $classRootNamespace = $this->getClassRootNamespace();
            $classRelativePath = Str::between(static::class, $classRootNamespace, '\\');
            $classRelativePath = Str::after($classRelativePath, '\\');
            $this->classSubFolders = $classRelativePath ? explode('\\', $classRelativePath) : [];
        }

        return $this->classSubFolders;
    }

    /**
     * get class basename without suffix
     */
    protected function getClassNameWithoutSuffix(): string
    {
        if (! isset($this->classNameWithoutSuffix)) {
            $classBasename = Str::afterLast(static::class, '\\');
            $classSuffix = $this->getClassSuffix();
            $this->classNameWithoutSuffix = Str::replaceLast($classSuffix, '', $classBasename);
        }

        return $this->classNameWithoutSuffix;
    }

    /**
     * get class suffix
     *
     * detect based class root namespace, assuming as singular
     */
    protected function getClassSuffix(): string
    {
        if (! isset($this->classSuffix)) {
            $classRootNamespace = $this->getClassRootNamespace();
            $this->classSuffix = Str::singular($classRootNamespace);
        }

        return $this->classSuffix;
    }

    /**
     * get class root namespace
     *
     * detect based parent class namespace,
     * assuming parent and child classes has same root namespace
     */
    protected function getClassRootNamespace(): string
    {
        if (! isset($this->classRootNamespace)) {
            $namespace = Str::beforeLast(self::class, '\\');
            $this->classRootNamespace = Str::afterLast($namespace, '\\');
        }

        return $this->classRootNamespace;
    }
}
