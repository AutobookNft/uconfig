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
    public function __construct($app)
    {
        parent::__construct($app);
        
        $this->publishes([
            __DIR__.'/../config/uconfig.php' => config_path('uconfig.php'),
        ], 'uconfig-config');
    }
    
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
     * @return void
     */
    public function register()
    {
        $this->app->bind('uconfig', function ($app) {
            $envLoader = new EnvLoader();
            $logger = new Logger();
            $databaseConnection = DatabaseConnection::getInstance($envLoader);
            return new UConfig($logger, $databaseConnection, $envLoader);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/uconfig.php' => config_path('uconfig.php'),
        ], 'config');
    }
} 