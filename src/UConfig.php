<?php

namespace UltraProject\UConfig;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use UltraProject\UConfig\Models\UConfig as UConfigModel;
use UltraProject\UConfig\Models\UConfigVersion;
use Illuminate\Support\Facades\Auth;
use UltraProject\UConfig\Logger;
use UltraProject\UConfig\DatabaseConnection;
use UltraProject\UConfig\EnvLoader;

class UConfig
{
    private $config = [];
    private $logger;
    private $databaseConnection;
    private $envLoader;
    private $app;

    public function __construct(Logger $logger, DatabaseConnection $databaseConnection, EnvLoader $envLoader, $app)
    {
        $this->logger = $logger;
        $this->databaseConnection = $databaseConnection;
        $this->envLoader = $envLoader;
        $this->app = $app;
    }

    public function loadConfig(string $source = null, array $options = []): void
    {
        // Inizializza l'array di configurazione
        $this->config = [];

        // Carica dal file di configurazione di default
        $this->loadFromFile($this->app->configPath('uconfig.php'));
        Log::channel('florenceegi')->info('Class: UconfigController. Method: loadConfig(). Action: Caricato da file di default');

        // Se è stato specificato un file sorgente, carica anche da quello
        if (is_string($source) && strpos($source, '.php') !== false) {
            $this->loadFromFile($source);
            Log::channel('florenceegi')->info('Class: UconfigController. Method: loadConfig(). Action: Caricato da file specificato: ' . $source);
        }

        // Carica le configurazioni dall'ambiente
        $this->loadFromEnv();
        Log::channel('florenceegi')->info('Class: UconfigController. Method: loadConfig(). Action: Caricato da .env');

        // Carica dal database
        if ($this->isValidDatabaseTable('uconfig')) {
            $this->loadFromDatabase($options);
            Log::channel('florenceegi')->info('Class: UconfigController. Method: loadConfig(). Action: Caricato dal database');
        }
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    private function loadFromFile(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->logger->error("File di configurazione non trovato o non leggibile: $filePath");
            throw new \RuntimeException("Impossibile caricare il file di configurazione: $filePath");
        }

        $config = require $filePath;

        // Unisce le configurazioni
        $this->config = array_merge($this->config, $config);
    }

    private function loadFromDatabase(): void
    {
        $configs = UConfigModel::all();

        foreach ($configs as $config) {
            // Decripta il valore
            $decryptedValue = Crypt::decryptString($config->value);

            // Decodifica il JSON se necessario
            $decodedValue = json_decode($decryptedValue, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $decryptedValue = $decodedValue;
            }

            $this->config[$config->key] = [
                'value' => $decryptedValue,
                'category' => $config->category,
                'note' => $config->note,
            ];
        }
    }

    public function saveToDatabase(): void
    {
        foreach ($this->config as $key => $configItem) {
            $value = $configItem['value'];
            $category = $configItem['category'] ?? null;
            $note = $configItem['note'] ?? null;

            if (!is_scalar($value)) {
                $value = json_encode($value);
            }

            // Cripta il valore
            $encryptedValue = Crypt::encryptString($value);

            // Salva o aggiorna la configurazione
            UConfigModel::updateOrCreate(
                ['key' => $key],
                [
                    'category' => $category,
                    'value' => $encryptedValue,
                    'note' => $note,
                ]
            );

            // Registra la versione
            UConfigVersion::create([
                'key' => $key,
                'category' => $category,
                'value' => $encryptedValue,
                'note' => $note,
                'user_id' => Auth::id(),
                'action' => 'updated',
            ]);
        }
    }

    /**
     * Recupera un valore di configurazione.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->config[$key] ?? $default;

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    /**
     * Imposta un valore di configurazione.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
        // Opzionale: salva il valore nella sorgente (file o database)
    }

    /**
     * Elimina una configurazione.
     *
     * @param string $key
     * @return void
     */
    public function delete(string $key): void
    {
        unset($this->config[$key]);
        // Opzionale: rimuovi il valore dalla sorgente (file o database)
    }

    /**
     * Recupera tutte le configurazioni.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->config;
    }

    private function isValidDatabaseTable(string $tableName): bool
    {
        try {
            $pdo = $this->databaseConnection->getPDOInstance();
            $result = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($tableName));
            return $result && $result->rowCount() > 0;
        } catch (\PDOException $e) {
            $this->logger->error("Errore durante la verifica della tabella: " . $e->getMessage());
            return false;
        }
    }

    private function loadFromEnv(): void
    {
        // Recupera tutte le variabili d'ambiente caricate dall'EnvLoader
        $envConfig = $this->envLoader->all();

        // Unisce le configurazioni
        $this->config = array_merge($this->config, $envConfig);
    }
} 