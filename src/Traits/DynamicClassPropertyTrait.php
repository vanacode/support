<?php

namespace Vanacode\Support\Traits;

use Illuminate\Support\Facades\App;
use Vanacode\Support\Exceptions\DynamicClassPropertyException;

trait DynamicClassPropertyTrait
{
    use ClassDetailsTrait;

    /**
     * Make property instance dynamically
     *
     * @throws DynamicClassPropertyException
     */
    private function makePropertyInstance(string $property, string $class, string $relativeNamespace, string $suffix, array $data = []): ?object
    {
        $className = $this->getPropertyClassNameAndCheckDepenedency($property, $class, $relativeNamespace, $suffix, $data);

        return $className ? App::make($className) : null;
    }

    private function getPropertyClassNameAndCheckDepenedency(string $property, string $class, string $relativeNamespace, string $suffix, array $data = []): ?string
    {
        $className = $this->getPropertyClassName($property, $relativeNamespace, $suffix, $data);
        if (empty($className)) {
            if (isset($data['nullable'])) {
                return null;
            }
            throw new DynamicClassPropertyException('Unable to detect class for property: '.$property);
        }

        if ($className != $class && ! is_subclass_of($className, $class)) {
            throw new DynamicClassPropertyException(sprintf('Detected property %s is not subclass of %s', $className, $class));
        }

        return $className;
    }

    /**
     *  Get class name where need inject this property
     *  Dynamically get property class name
     *  first check has {property}Class property
     *  dynamically get based $classRoot and inject it
     *
     * @throws DynamicClassPropertyException
     */
    private function getPropertyClassName(string $property, string $relativeNamespace, string $suffix, array $data): ?string
    {
        $classPropertyName = $property.'Class';

        if (isset($this->{$classPropertyName})) {
            return $this->{$classPropertyName};
        }

        $relativeNamespace = $this->getRelativeNamespace($relativeNamespace);
        $parts = $this->getClassRelativePaths();
        $classPrefix = $this->getClassWithoutSuffix();
        $classBasename = $classPrefix.$suffix;

        while ($parts) {
            $className = $relativeNamespace.implode('\\', $parts).'\\'.$classBasename;

            if (class_exists($className)) {
                return $className;
            }
            array_pop($parts);
        }

        $className = $relativeNamespace.$classBasename;

        if (class_exists($className)) {
            return $className;
        }

        if (isset($data['default'])) {
            return $data['default'];
        }

        return null;
    }
}
