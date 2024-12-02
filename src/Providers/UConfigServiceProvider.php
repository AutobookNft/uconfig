<?php

namespace UltraProject\UConfig\Providers;

use Illuminate\Support\ServiceProvider;
use UltraProject\UConfig\UConfig;
use UltraProject\UConfig\EnvLoader;
use UltraProject\UConfig\Http\Middleware\CheckConfigManagerRole;



class UConfigServiceProvider extends ServiceProvider
{
    /**
     * Registra i servizi nel contenitore.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('uconfig', function ($app) {
            $envLoader = new EnvLoader();
            return new UConfig($envLoader);
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
            
            // Controlla e gestisci il messaggio di pubblicazione iniziale
            $this->handleInitialPublicationMessage();

            $this->publishes([
                // Pubblica i file delle migrazioni
                __DIR__.'/../database/migrations/create_uconfig_table.php.stub' => $this->app->databasePath('migrations/' . now()->format('Y_m_d_His_u') . '_create_uconfig_table.php'),
                __DIR__.'/../database/migrations/create_uconfig_versions_table.php.stub' => $this->app->databasePath('migrations/' . now()->format('Y_m_d_His_u') . '_create_uconfig_versions_table.php'),
                __DIR__.'/../database/migrations/create_uconfig_audit_table.php.stub' => $this->app->databasePath('migrations/' . now()->format('Y_m_d_His_u') . '_create_uconfig_audit_table.php'),
                // Pubblica le viste
                __DIR__.'/../resources/views' => resource_path('views/vendor/uconfig'),
                // Pubblica il file di configurazione
                __DIR__.'/../config/uconfig.php' => $this->app->configPath('uconfig.php'),
                __DIR__.'/../routes/web.php' => $this->app->basePath('routes/uconfig.php'),
                // Pubblica il file di alias
                __DIR__.'/../config/aliases.php' => base_path('bootstrap/aliases.php'),
            ], 'uconfig-resources'); // Usa un unico tag per tutte le risorse
            
        }

        // Carica le rotte pubblicate o quelle predefinite
        if (file_exists(base_path('routes/uconfig.php'))) {
            $this->loadRoutesFrom(base_path('routes/uconfig.php'));
        } else {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Registra il middleware
        $this->app['router']->aliasMiddleware('uconfig.check_role', CheckConfigManagerRole::class);
    }

    
   /**
     * Handle the initial publication message.
     *
     * This checks if the 'initial_publication_message' configuration is set to true.
     * If the key is missing, it will be created with default values.
     * After displaying the message, the value will be set to false.
     */
    private function handleInitialPublicationMessage(): void
    {
        /** @var UConfig $uconfig */
        $uconfig = $this->app->make('uconfig');

        // Recupera il valore della configurazione, aggiungendola se mancante
        $showMessage = $uconfig->get('initial_publication_message', null);

        if ($showMessage === null) {
            // Chiave mancante: creala con il valore di default
            $uconfig->set('initial_publication_message', true, 'system');
            $showMessage = true;
        }

        if ($showMessage) {
            // Mostra il messaggio nella console
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            $output->writeln('<info>Attenzione: il file aliases.php esiste già. Assicurati di aggiungere la seguente riga:</info>');
            $output->writeln("'UConfig' => UltraProject\\UConfig\\Facades\\UConfig::class,");
            $output->writeln('<info>Per ulteriori dettagli, fai riferimento alla documentazione nella sezione Facades: UConfig.</info>');

            // Imposta il valore su false dopo aver mostrato il messaggio
            $uconfig->set('initial_publication_message', false, 'system');
        }
    }
  
} 