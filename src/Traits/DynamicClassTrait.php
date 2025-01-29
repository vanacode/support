<?php

namespace Vanacode\Support\Traits;

use Illuminate\Support\Facades\App;

trait DynamicClassTrait
{
    use ClassDetailsTrait;

    /**
     * Make class instance dynamically based caller sub folders first match
     */
    protected function makeClassDynamically(string $relativeNamespace, string $suffix, array $data = []): ?object
    {
        $className = $this->getClassNameDynamically($relativeNamespace, $suffix, $data['sub_folders'] ?? null, $data['default'] ?? null);

        return $className ? App::make($className, $data['parameters'] ?? []) : null;
    }

    /**
     *  Get class name dynamically based caller sub folders first match
     */
    protected function getClassNameDynamically(string $relativeNamespace, string $suffix, ?array $subFolders = null, ?string $default = null): ?string
    {
        $rootNamespace = $this->getTargetRootNamespace($relativeNamespace);
        $subFolders = $subFolders ?? $this->getClassSubFolders();
        $classBasename = $this->getClassNameWithoutSuffix().$suffix;

        while ($subFolders) {
            $className = $rootNamespace.implode('\\', $subFolders).'\\'.$classBasename;

            if (class_exists($className)) {
                return $className;
            }
            array_pop($subFolders);
        }

        $className = $rootNamespace.$classBasename;

        return class_exists($className) ? $className : $default;
    }
}
