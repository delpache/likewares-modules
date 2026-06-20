<?php

namespace Likewares\Module\Providers;

use Likewares\Module\Contracts\IActivatorInterface;
use Likewares\Module\Contracts\IRepositoryInterface;
use Likewares\Module\Lumen\LumenFileRepository;
use Likewares\Module\Support\Stub;

class LumenServiceProvider extends MainServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->setupStubPath();
    }

    /**
     * Register all modules.
     */
    public function register()
    {
        $this->registerNamespaces();
        $this->registerServices();
        $this->registerModules();
        $this->registerProviders();
    }

    /**
     * Setup stub path.
     */
    public function setupStubPath()
    {
        Stub::setBasePath(__DIR__ . '/Commands/stubs');

        if (app('module')->config('stubs.enabled') === true) {
            Stub::setBasePath(app('module')->config('stubs.path'));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function registerServices()
    {
        $this->app->singleton(IRepositoryInterface::class, function ($app) {
            $path = $app['config']->get('module.paths.modules');

            return new LumenFileRepository($app, $path);
        });

        $this->app->singleton(IActivatorInterface::class, function ($app) {
            $class = $app['config']->get('module.activator');

            return new $class($app);
        });

        $this->app->alias(IRepositoryInterface::class, 'module');
    }
}
