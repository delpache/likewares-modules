<?php

namespace Likewares\Module\Providers;

use Likewares\Module\Contracts\IRepositoryInterface;
use Likewares\Module\Laravel\LaravelFileRepository;
use Illuminate\Support\ServiceProvider;

class ContractsServiceProvider extends ServiceProvider
{
    /**
     * Register some binding.
     */
    public function register()
    {
        $this->app->bind(IRepositoryInterface::class, LaravelFileRepository::class);
    }
}
