<?php

namespace Fabio\UConfig\Facades;

use Illuminate\Support\Facades\Facade;

class UConfig extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'uconfig'; // Questo deve corrispondere al binding nel service provider
    }
} 