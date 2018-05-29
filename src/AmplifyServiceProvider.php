<?php

/**
 * @author: Nriagu Dubem <nriagudubem@gmail.com>
 * Date: 11/4/17
 * Time: 11:28 PM
 */

namespace Dubems\Amplify;

use Illuminate\Support\ServiceProvider;

class AmplifyServiceProvider extends ServiceProvider
{

    protected $defer = false;


    public function boot()
    {
        $config = realpath(__DIR__.'/../resources/config/amplify.php');

        $this->publishes([
            $config => config_path('amplify.php')
        ]);
    }

    public function register()
    {
        $this->app->bind('laravel-amplify',function(){
            return new Amplify;
        });
    }

    /**
     * Get the services provided by the provider
     * @return array
     */
    public function provides()
    {
        return ['laravel-amplify'];
    }
}