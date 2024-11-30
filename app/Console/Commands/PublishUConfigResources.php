<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PublishUConfigResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uconfig:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pubblica le risorse del pacchetto UConfig';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->call('vendor:publish', ['--tag' => 'uconfig-resources']);

        if (file_exists(base_path('bootstrap/aliases.php'))) {
            $this->info('Attenzione: il file aliases.php esiste già. Assicurati di aggiungere la seguente riga:');
            $this->info("'UConfig' => UltraProject\\UConfig\\Facades\\UConfig::class,");
            $this->info('Per ulteriori dettagli, fai riferimento alla documentazione nella sezione Facades: UConfig.');
        }

        return 0;
    }
} 