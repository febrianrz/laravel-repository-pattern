<?php

namespace Febrianrz\RepositoryPattern\Provider;

use Febrianrz\RepositoryPattern\Command\MakeServiceCommand;
use Illuminate\Support\ServiceProvider;

class RepositoryPatternProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/repository-pattern.php' => config_path('repository-pattern.php'),
        ]);
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->mergeConfigFrom(__DIR__.'/../../config/repository-pattern.php','repository-pattern');
        $this->commands([
            MakeServiceCommand::class
        ]);
    }
}
