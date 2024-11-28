<?php

namespace Fabio\UConfig\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Fabio\UConfig\UConfig;
use Fabio\UConfig\Logger;
use Fabio\UConfig\DatabaseConnection;
use Fabio\UConfig\EnvLoader;

class UConfigServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Registra i servizi nel contenitore.
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

    /**
     * Esegue le azioni di bootstrap dei servizi.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/uconfig.php' => config_path('uconfig.php'),
        ], 'uconfig-config');

        // Pubblica la migration
        if ($this->app->runningInConsole()) {
            if (!class_exists('CreateUconfigTable')) {
                $this->publishes([
                    __DIR__.'/../../database/migrations/create_uconfig_table.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_create_uconfig_table.php'),
                ], 'uconfig-migrations');
            }
        }
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
     * Ottieni i servizi forniti dal provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['uconfig'];
    }
} 