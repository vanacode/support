<?php

namespace Vanacode\Support\Traits;

use Illuminate\Support\Str;

trait ClassDetailsTrait
{
    private string $classRootNamespace;

    private array $classRelativePaths;

    private string $classWithoutSuffix;

    private function getClassRelativePaths(): array
    {
        if (! isset($this->classRelativePaths)) {
            $this->processClassDetails();
        }

        return $this->classRelativePaths;
    }

    private function getClassRootFolder(): string
    {
        $path = Str::beforeLast(self::class, '\\');

        return Str::afterLast($path, '\\');
    }

    private function getRelativeNamespace(string $relativeNamespace): string
    {
        $rootNamespace = $this->getRootNamespace().'\\';

        return $relativeNamespace ? $rootNamespace.$relativeNamespace.'\\' : $rootNamespace;
    }

    private function getRootNamespace(): string
    {
        if (! isset($this->classRootNamespace)) {
            $this->processClassDetails();
        }

        return $this->classRootNamespace;
    }

    private function getClassWithoutSuffix(): string
    {
        if (! isset($this->classWithoutSuffix)) {
            $this->processClassDetails();
        }

        return $this->classWithoutSuffix;
    }

    private function processClassDetails(): void
    {
        $classRootFolder = $this->getClassRootFolder();
        $classRelativePath = Str::between(static::class, $classRootFolder, Str::singular($classRootFolder));
        $classRelativePath = Str::after($classRelativePath, '\\');
        $classRelativePaths = explode('\\', $classRelativePath);

        $this->classWithoutSuffix = array_pop($classRelativePaths);
        $this->classRelativePaths = $classRelativePaths;
        $this->classRootNamespace = Str::before(static::class, '\\');
    }
}
