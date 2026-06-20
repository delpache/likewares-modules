<?php

namespace Likewares\Module\Providers;

use Likewares\Module\Contracts\IActivatorInterface;
use Likewares\Module\Contracts\IRepositoryInterface;
use Likewares\Module\Laravel\LaravelFileRepository;
use Likewares\Module\Support\Stub;

class LaravelServiceProvider extends MainServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->registerNamespaces();
        $this->registerModules();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerServices();
        $this->setupStubPath();
        $this->registerProviders();
        $this->registerConfig();
    }

    /**
     * Setup stub path.
     */
    public function setupStubPath()
    {
        $path = $this->app['config']->get('module.stubs.path') ?? __DIR__ . '/Commands/stubs';

        Stub::setBasePath($path);

        $this->app->booted(function ($app) {
            $repository = $app[IRepositoryInterface::class];

            if ($repository->config('stubs.enabled') === true) {
                Stub::setBasePath($repository->config('stubs.path'));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function registerServices()
    {
        $this->app->singleton(IRepositoryInterface::class, function ($app) {
            $path = $app['config']->get('module.paths.modules');

            return new LaravelFileRepository($app, $path);
        });

        $this->app->singleton(IActivatorInterface::class, function ($app) {
            $class = $app['config']->get('module.activator');

            return new $class($app);
        });

        $this->app->alias(IRepositoryInterface::class, 'module');
    }

    /**
     * Register module config.
     */
    public function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/module.php', 'module');
    }
}
