<?php

namespace UltraProject\UConfig\Facades;

use Illuminate\Support\Facades\Facade;

class UConfig extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'uconfig'; // Deve corrispondere al binding nel service provider
    }
} 