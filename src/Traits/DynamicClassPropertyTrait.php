<?php

namespace Vanacode\Support\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Exception;

trait DynamicClassPropertyTrait
{
    use ClassDetailsTrait;

    /**
     * Make property instance dynamically
     *
     * @throws Exception
     */
    private function makePropertyInstance(string $property, string $class, string $relativeNamespace, string $suffix, array $data = []): object|null
    {
        $className = $this->getPropertyClassName($property, $relativeNamespace, $suffix, $data);
        if (isset($data['nullable']) && empty($className)) {
            return null;
        }
        if ($className != $class && !is_subclass_of($className, $class)) {
            throw new Exception(sprintf('Detected property %s is not subclass of %s', $className, $class));
        }
        return App::make($className);
    }

    /**
     *  Get class name where need inject this property
     *  Dynamically get property class name
     *  first check has {property}Class property
     *  dynamically get based $classRoot and inject it
     *
     * @throws Exception
     */
    private function getPropertyClassName(string $property, string $relativeNamespace, string $suffix, array $data): ?string
    {
        $classPropertyName = $property . 'Class';

        if (isset($this->{$classPropertyName})) {
            return $this->{$classPropertyName};
        }

        $relativeNamespace = $this->getRelativeNamespace($relativeNamespace);
        $parts = $this->getClassRelativePaths();
        $classPrefix = $this->getClassWithoutSuffix();
        $classBasename = $classPrefix . $suffix;

        while ($parts) {
            $className = $relativeNamespace . implode('\\', $parts) . '\\' . $classBasename;

            if (class_exists($className)) {
                return $className;
            }
            array_pop($parts);
        }

        $className = $relativeNamespace . $classBasename;

        if (class_exists($className)) {
            return $className;
        }

        if (isset($data['default'])) {
            return $data['default'];
        }

        if (!isset($data['nullable'])) {
            throw new \Exception('Unable to detect class for property: ' . $property);
        }

        return null;
    }
}
