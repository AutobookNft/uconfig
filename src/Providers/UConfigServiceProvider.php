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
         // Pubblica le risorse
        if ($this->app->runningInConsole()) {
            $this->publishes([
                // Pubblica le migrazioni
                __DIR__.'/../../database/migrations/create_uconfig_table.php.stub' => $this->app->databasePath('migrations/' . date('Y_m_d_His') . '_create_uconfig_table.php'),
                __DIR__.'/../../database/migrations/create_uconfig_versions_table.php.stub' => $this->app->databasePath('migrations/' . date('Y_m_d_His') . '_create_uconfig_versions_table.php'),
                __DIR__.'/../../database/migrations/create_uconfig_audit_table.php.stub' => $this->app->databasePath('migrations/' . date('Y_m_d_His') . '_create_uconfig_audit_table.php'),
                // Pubblica le viste
                __DIR__.'/../../resources/views' => resource_path('views/vendor/uconfig'),
                // Pubblica il file di configurazione
                __DIR__.'/../../config/uconfig.php' => $this->app->configPath('uconfig.php'),
                __DIR__.'/../../routes/web.php' => $this->app->basePath('routes/uconfig.php'),
            ], 'uconfig-resources'); // Usa un unico tag per tutte le risorse
        }

        // Carica le rotte pubblicate o quelle predefinite
        if (file_exists(base_path('routes/uconfig.php'))) {
            $this->loadRoutesFrom(base_path('routes/uconfig.php'));
        } else {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        }

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