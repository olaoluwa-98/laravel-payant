<?php

/*
 * This file is part of the Laravel Payant package.
 *
 * (c) Emmanuel Awotunde <awotunde.emmanuel1@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Olaoluwa98\Payant;

use Illuminate\Support\ServiceProvider;

class PayantServiceProvider extends ServiceProvider
{

    /*
    * Indicates if loading of the provider is deferred.
    *
    * @var bool
    */
    protected $defer = false;

    /**
    * Publishes all the config file this package needs to function
    */
    public function boot()
    {
        $config = realpath(__DIR__.'/../resources/config/payant.php');

        $this->publishes([
            $config => config_path('payant.php')
        ]);
    }

    /**
    * Register the application services.
    */
    public function register()
    {
        $this->app->bind('laravel-payant', function () {

            return new Payant;

        });
    }

    /**
    * Get the services provided by the provider
    * @return array
    */
    public function provides()
    {
        return ['laravel-payant'];
    }
}
