<?php

namespace Vanacode\Support\Traits;

use Illuminate\Support\Str;

trait ClassDetailsTrait
{
    /**
     * core_namespace:
     *       it is application or package namespace
     * root_namespace:
     *      detect based parent class namespace,
     *      assuming parent and child classes has same root namespace
     * resource_name:
     *      class resource name,
     * sub_folders:
     *      lass sub folders after root folder
     *      detect based class root namespace and basename
     */
    protected array $classDetails = [];

    /**
     * get class core namespace
     *
     * detect based current class namespace
     * it is application or package namespace
     */
    protected function getClassCoreNamespace(): string
    {
        if (! array_key_exists('core_namespace', $this->classDetails)) {
            $this->classDetails['core_namespace'] = Str::before(static::class, '\\').'\\';
        }

        return $this->classDetails['core_namespace'];
    }

    /**
     * get class root namespace
     *
     * detect based parent class namespace,
     * assuming parent and child classes has same root namespace
     */
    protected function getClassRootNamespace(): string
    {
        if (! array_key_exists('root_namespace', $this->classDetails)) {
            $namespace = Str::beforeLast(self::class, '\\');
            $this->classDetails['root_namespace'] = Str::afterLast($namespace, '\\');
        }

        return $this->classDetails['root_namespace'];
    }

    /**
     * detect application(package) root namespace and concat relative namespace
     */
    protected function getTargetRootNamespace(string $relativeNamespace): string
    {
        $coreNamespace = $this->getClassCoreNamespace();

        return $coreNamespace ? $coreNamespace.$relativeNamespace.'\\' : $coreNamespace;
    }

    /**
     * get class basename without suffix
     */
    protected function getClassResourceName(): string
    {
        if (! array_key_exists('resource_name', $this->classDetails)) {
            $classBasename = Str::afterLast(static::class, '\\');
            $classRootNamespace = $this->getClassRootNamespace();
            $classSuffix = Str::singular($classRootNamespace);
            $this->classDetails['resource_name'] = Str::replaceLast($classSuffix, '', $classBasename);
        }

        return $this->classDetails['resource_name'];
    }

    /**
     * Get class sub folders after class root folder
     *
     * detect based class root namespace and basename
     */
    protected function getClassSubFolders(): array
    {
        if (! array_key_exists('sub_folders', $this->classDetails)) {
            $classRootNamespace = $this->getClassRootNamespace();
            $classRelativePath = Str::between(static::class, $classRootNamespace, '\\');
            $classRelativePath = Str::after($classRelativePath, '\\');
            $this->classDetails['sub_folders'] = $classRelativePath ? explode('\\', $classRelativePath) : [];
        }

        return $this->classDetails['sub_folders'];
    }
}
