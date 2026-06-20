<?php

namespace Likewares\Module\Providers;

use Likewares\Module\Contracts\IRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class BootstrapServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->app[IRepositoryInterface::class]->boot();
    }

    /**
     * Register the provider.
     */
    public function register()
    {
        $this->app[IRepositoryInterface::class]->register();
    }
}
