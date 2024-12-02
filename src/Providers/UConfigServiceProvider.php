<?php

namespace UltraProject\UConfig\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use UltraProject\UConfig\UConfig;
use UltraProject\UConfig\EnvLoader;
use UltraProject\UConfig\Facades\UConfig as FacadesUConfig;
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

        // Non eseguire nulla se il comando è 'queue:work' o 'queue:listen'
        $firstArgument = $_SERVER['argv'][1] ?? null;
        if ($firstArgument === 'queue:work' || $firstArgument === 'queue:listen') {
            // Log::info('UConfigServiceProvider firstArgument: ' . $firstArgument );
            return;
        }else{
            // Log::info('UConfigServiceProvider Boot' );
        }

        // Esegue le seguenti azioni solo se l'applicazione è in esecuzione da riga di comando
        if (app()->runningInConsole()) {

            $this->handleInitialPublicationMessage();

            $this->publishTheFilea();
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
     * Gestisce la visualizzazione di un messaggio iniziale durante la pubblicazione delle risorse del pacchetto.
     *
     * Questo metodo verifica se il comando 'vendor:publish' con il tag 'uconfig-resources' è stato eseguito.
     * Se sì, controlla lo stato della configurazione 'initial_publication_message' e, se necessario,
     * visualizza un messaggio informativo e aggiorna lo stato della configurazione.
     *
     * @param \App\Services\UConfig $uconfig Istanza del servizio UConfig utilizzato per gestire le configurazioni.
     * @return void
     */
    private function handleInitialPublicationMessage(): void
    {
        // Recupera gli argomenti passati al comando corrente
        $arguments = $_SERVER['argv'] ?? [];
        // $uconfig = $this->app->make('uconfig');

        // Verifica se 'vendor:publish' è tra gli argomenti
        if (in_array('vendor:publish', $arguments)) {
            // Controlla se è presente un argomento che inizia con '--tag='
            foreach ($arguments as $arg) {
                if (strpos($arg, '--tag=') === 0) {
                    // Estrae i tag specificati e li divide in un array
                    $tags = explode('=', $arg)[1];
                    $tagsArray = explode(',', $tags);

                    // Verifica se 'uconfig-resources' è tra i tag specificati
                    if (in_array('uconfig-resources', $tagsArray)) {
                        // Recupera il valore corrente della configurazione 'initial_publication_message'
                        $showMessage =  FacadesUConfig::get('initial_publication_message', null);

                        // Se il messaggio non è stato ancora mostrato (0 o null)
                        if ($showMessage == 0 || $showMessage === null) {
                            // Imposta temporaneamente la configurazione a "0"
                            FacadesUConfig::set('initial_publication_message', "0", 'system');

                            // Crea un'istanza per l'output sulla console
                            $output = new \Symfony\Component\Console\Output\ConsoleOutput();

                            // Visualizza il messaggio informativo sulla console
                            $output->writeln('<info>Attenzione: il file aliases.php esiste già. Assicurati di aggiungere la seguente riga:</info>');
                            $output->writeln("'UConfig' => UltraProject\\UConfig\\Facades\\UConfig::class,");
                            $output->writeln('<info>Per ulteriori dettagli, fai riferimento alla documentazione nella sezione Facades: UConfig.</info>');

                            // Aggiorna la configurazione per indicare che il messaggio è stato mostrato
                            FacadesUConfig::set('initial_publication_message', "1", 'system');

                            // Registra nel log che il messaggio è stato mostrato
                            Log::info('handleInitialPublicationMessage showMessage dopo: ' . json_encode($showMessage));
                        }
                    }
                }
            }
        }
    }

    private function publishTheFilea(): void{
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

}
