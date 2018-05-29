<?php

namespace Dubems\Amplify\Facades;

use Illuminate\Support\Facades\Facade;

class Amplify extends Facade{
    
    /**
     * Get the registered name of the component
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-amplify';
    }

}

