<?php

namespace Fabio\UConfig;

use Illuminate\Support\Facades\Log;
use PDO;

class UConfig
{
    private $config = [];
    private $logger;
    private $databaseConnection;
    private $envLoader;

    public function __construct(Logger $logger, DatabaseConnection $databaseConnection, EnvLoader $envLoader)
    {
        $this->logger = $logger;
        $this->databaseConnection = $databaseConnection;
        $this->envLoader = $envLoader;
    }

    public function loadConfig(string $source = null, array $options = []): void
    {
        // Inizializza l'array di configurazione
        $this->config = [];

        // Carica dal file di configurazione di default
        $this->loadFromFile(config_path('uconfig.php'));
        Log::channel('florenceegi')->info('Class: UconfigController. Method: loadConfig(). Action: Caricato da file di default');

        // Se è stato specificato un file sorgente, carica anche da quello
        if (is_string($source) && strpos($source, '.php') !== false) {
            $this->loadFromFile($source);
            Log::channel('florenceegi')->info('Class: UconfigController. Method: loadConfig(). Action: Caricato da file specificato: ' . $source);
        }

        // Carica dal database
        if ($this->isValidDatabaseTable('uconfig')) {
            $this->loadFromDatabase('uconfig', $options);
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

    private function loadFromDatabase(string $tableName, array $options = []): void
    {
        try {
            $pdo = $this->databaseConnection->getPDOInstance();
            $stmt = $pdo->prepare("SELECT `key`, `value` FROM $tableName");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Decodifica eventuali valori JSON
            foreach ($data as $key => $value) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$key] = $decoded;
                } else {
                    $data[$key] = $value;
                }
            }

            // Unisce le configurazioni
            $this->config = array_merge($this->config, $data);
        } catch (\PDOException $e) {
            $this->logger->error("Errore nel caricamento della configurazione dal database: " . $e->getMessage());
            throw new \RuntimeException("Impossibile caricare la configurazione dal database.");
        }
    }

    public function saveToDatabase(): void
    {
        try {
            $pdo = $this->databaseConnection->getPDOInstance();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO uconfig (`key`, `value`) VALUES (:key, :value)
                                   ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

            foreach ($this->config as $key => $value) {
                if (!is_scalar($value)) {
                    $value = json_encode($value);
                }

                $stmt->execute([':key' => $key, ':value' => $value]);
            }

            $pdo->commit();
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Errore nel salvataggio della configurazione nel database: " . $e->getMessage());
            throw new \RuntimeException("Impossibile salvare la configurazione nel database.");
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
} 