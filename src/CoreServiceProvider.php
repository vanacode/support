<?php

namespace Vanacode\Support;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class CoreServiceProvider extends ServiceProvider
{
    public string $packageName;

    public function registerFunctionsBy(string $callPath, string $path = 'helpers.php'): void
    {
        require_once $this->getPackageSrcPath($callPath).$path;
    }

    public function registerConstantsBy(string $callPath, string $path = 'constants.php'): void
    {
        require_once $this->getPackageSrcPath($callPath).$path;
    }

    protected function registerAliases(array $aliases): void
    {
        foreach ($aliases as $alias => $class) {
            $this->registerAlias($alias, $class);
        }
    }

    protected function registerAlias(string $alias, string $class): void
    {
        $aliases = config('app.aliases');
        $aliases[$alias] = $class;
        AliasLoader::getInstance($aliases)->register();
    }

    protected function registerSingletons(array $singletons): void
    {
        foreach ($singletons as $singleton => $class) {
            $this->registerSingleton($singleton, $class);
        }
    }

    protected function registerSingleton(string $singleton, string $class): void
    {
        $this->app->singleton($singleton, function ($app) use ($class) {
            return $app->make($class);
        });
    }

    /**
     * Merge config file by default with snake_case of package name like [package_name] and publish it with tag [project-name]-config
     */
    public function mergeAndPublishConfigBy(string $callPath, string $configName = '', bool $isPublish = true): void
    {
        if ($this->isConfigurationCached() && ! $this->runningInConsole()) {
            return;
        }

        $configName = $configName ?: $this->getPackageName($callPath);
        $configPath = $this->getPackageConfigPath($callPath).$configName.'.php';

        $this->mergeAndPublishConfigFrom($configPath, $configName, $isPublish);
    }

    /**
     * Merge config file by given name and publish it with tag [name]-config
     */
    public function mergeAndPublishConfigFrom(string $configPath, string $configName, bool $isPublish = true): void
    {
        $this->mergeConfigFrom($configPath, $configName);

        if ($isPublish && $this->runningInConsole()) {
            $group = $this->getPublishGroup($configName, 'config');
            $this->publishes([$configPath => config_path($configName.'.php')], $group);
        }
    }

    protected function loadRoutesBy(string $callPath, string $path = 'web.php'): void
    {
        if (! $this->routesAreCached()) {
            $routePath = $this->getPackageRoutesPath($callPath);
            $this->loadRoutesFrom($routePath.$path);
        }
    }

    protected function pushMiddlewareToGroup(string $middleware, string $group = 'web'): void
    {
        $router = $this->app[Registrar::class]; // $this->app['router'];
        $router->pushMiddlewareToGroup($group, $middleware);
    }

    protected function registerMiddleware(string $middleware): void
    {
        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware($middleware);
    }

    protected function registerCommands(array|string $commands): void
    {
        $commands = Arr::wrap($commands);

        $registerAble = [];
        $isRunningInConsole = ! $this->runningInConsole();
        foreach ($commands as $command => $onlyConsole) {
            if (is_string($onlyConsole)) {
                $registerAble[] = $onlyConsole;
            } elseif ($onlyConsole && $isRunningInConsole) {
                $registerAble[] = $command;
            }
        }
        $this->commands($registerAble);
    }

    protected function loadMigrationsBy(string $callPath): void
    {
        if ($this->runningInConsole()) {
            $migrationsPath = $this->getPackageMigrationsPath($callPath);
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    protected function loadViewsBy(string $callPath, string $namespace = '', bool $isPublish = true): void
    {
        $viewPath = $this->getPackageViewPath($callPath);
        $namespace = $namespace ?: Str::slug($this->getPackageName($callPath));
        $this->loadViewsFrom($viewPath, $namespace);

        if ($isPublish && $this->runningInConsole()) {
            $viewVendorPath = $this->getApplicationViewVendorPath($namespace);
            $group = $this->getPublishGroup($namespace, 'views');
            $this->publishes([$viewPath => $viewVendorPath], $group);
        }
    }

    protected function loadTranslationsBy(string $callPath, string $namespace = '', bool $isPublish = true): void
    {
        $langPath = $this->getPackageLangPath($callPath);
        $namespace = $namespace ?: $this->getPackageName($callPath);

        $this->loadTranslationsFrom($langPath, $namespace);

        if ($isPublish && $this->runningInConsole()) {
            $langVendorPath = $this->getApplicationLangVendorPath($namespace);
            $group = $this->getPublishGroup($namespace, 'translations');
            $this->publishes([$langPath => $langVendorPath], $group);
        }
    }

    protected function getApplicationLangVendorPath(string $path): string
    {
        return $this->app->langPath('vendor/'.$path);
    }

    protected function getApplicationViewVendorPath(string $path): string
    {
        return $this->app->viewPath('vendor/'.$path);
    }

    protected function getPackageSrcPath(string $callPath): string
    {
        return $this->getPackageSubPath($callPath, 'src');
    }

    protected function getPackageConfigPath(string $callPath): string
    {
        return $this->getPackageSubPath($callPath, 'config');
    }

    protected function getPackageRoutesPath(string $callPath): string
    {
        return $this->getPackageSubPath($callPath, 'routes');
    }

    protected function getPackageResourcesPath(string $callPath): string
    {
        return $this->getPackageSubPath($callPath, 'resources');
    }

    protected function getPackageLangPath(string $callPath): string
    {
        return $this->getPackageSubPath($callPath, 'lang');
    }

    protected function getPackageDatabasePath(string $callPath): string
    {
        return $this->getPackageSubPath($callPath, 'database');
    }

    protected function getPackageMigrationsPath(string $callPath, string $migrations = 'migrations'): string
    {
        return $this->getPackageDatabasePath($callPath).$migrations;
    }

    protected function getPackageViewPath(string $callPath, string $view = 'views')
    {
        return $this->getPackageResourcesPath($callPath).$view;
    }

    protected function getPackageSubPath(string $callPath, string $subPath): string
    {
        return $this->getPackagePath($callPath).$subPath.DIRECTORY_SEPARATOR;
    }

    protected function getPackagePath(string $callPath): string
    {
        return Str::before($callPath, 'src');
    }

    protected function getPackageName(string $callPath): string
    {
        if (! isset($this->packageName)) {
            $this->packageName = $this->detectPackageName($callPath);
        }

        return $this->packageName;
    }

    protected function detectPackageName(string $callPath): string
    {
        $packagePath = $this->getPackagePath($callPath);
        $packagePath = trim($packagePath, DIRECTORY_SEPARATOR);
        $packageName = Str::afterLast($packagePath, DIRECTORY_SEPARATOR);

        return Str::snake($packageName);
    }

    protected function getPublishGroup(string $resource, string $groupSuffix): string
    {
        return Str::slug($resource).'-'.$groupSuffix;
    }

    protected function isConfigurationCached(): bool
    {
        return $this->app instanceof CachesConfiguration && $this->app->configurationIsCached();
    }

    protected function routesAreCached(): bool
    {
        return $this->app instanceof CachesRoutes && $this->app->routesAreCached();
    }

    protected function runningInConsole(): bool
    {
        return $this->app->runningInConsole();
    }
}
