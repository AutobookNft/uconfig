<?php

namespace UltraProject\UConfig\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use UltraProject\UConfig\UConfig;
use UltraProject\UConfig\Logger;
use UltraProject\UConfig\DatabaseConnection;
use UltraProject\UConfig\EnvLoader;
use UltraProject\UConfig\Http\Middleware\CheckConfigManagerRole;

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
            return new UConfig($logger, $databaseConnection, $envLoader, $app);
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
            __DIR__.'/../../config/uconfig.php' => $this->app->configPath('uconfig.php'),
        ], 'uconfig-config');

        // Pubblica le migrazioni
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../database/migrations/create_uconfig_table.php.stub' => $this->app->databasePath('migrations/' . date('Y_m_d_His') . '_create_uconfig_table.php'),
            ], 'uconfig-migrations');

            $this->publishes([
                __DIR__.'/../../database/migrations/create_uconfig_versions_table.php.stub' => $this->app->databasePath('migrations/' . date('Y_m_d_His') . '_create_uconfig_versions_table.php'),
            ], 'uconfig-migrations');

            $this->publishes([
                __DIR__.'/../../database/migrations/create_uconfig_audit_table.php.stub' => $this->app->databasePath('migrations/' . date('Y_m_d_His') . '_create_uconfig_audit_table.php'),
            ], 'uconfig-migrations');
        }

        // Pubblica le rotte
        $this->publishes([
            __DIR__.'/../../routes/web.php' => base_path('routes/uconfig.php'),
        ], 'uconfig-routes');

        // Carica le rotte pubblicate o quelle predefinite
        if (file_exists(base_path('routes/uconfig.php'))) {
            $this->loadRoutesFrom(base_path('routes/uconfig.php'));
        } else {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        }

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'uconfig');

        // Registra il middleware
        $this->app['router']->aliasMiddleware('uconfig.check_role', CheckConfigManagerRole::class);
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