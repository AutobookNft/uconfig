<?php

namespace Fabio\UConfig\Providers;

use Fabio\UConfig\UConfig;
use Fabio\UConfig\Logger;
use Fabio\UConfig\DatabaseConnection;
use Fabio\UConfig\EnvLoader;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class UConfigServiceProvider extends ServiceProvider implements DeferrableProvider
{
    
     /**
     * Determina se il provider è "differibile".
     *
     * @return bool
     */
    public function isDeferred()
    {
        return true;
    }
    
    
    /**
     * Register the UConfig service in the container.
     *
     * @param Container $container
     * @return void
     */
    public function register($container)
    {
        $container->bind('uconfig', function () {
            $envLoader = new EnvLoader();
            $logger = new Logger();
            $databaseConnection = DatabaseConnection::getInstance($envLoader);
            return new UConfig($logger, $databaseConnection, $envLoader);
        });
    }
} 