<?php

namespace Fabio\UConfig;

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
        if ($source === null) {
            // Carica da file di default
            $this->loadFromFile(__DIR__ . '/../config/config_manager.php');
        } elseif (is_string($source) && strpos($source, '.php') !== false) {
            $this->loadFromFile($source);
        } elseif (is_string($source) && $this->isValidDatabaseTable($source)) {
            $this->loadFromDatabase($source, $options);
        } else {
            // Gestione dell'errore per sorgente non valida
            $this->logger->error("Sorgente di configurazione non valida: " . var_export($source, true));
            throw new \InvalidArgumentException("La sorgente di configurazione specificata non è valida.");
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

        $this->config = require $filePath;
    }

    private function loadFromDatabase(string $tableName, array $options = []): void
    {
        try {
            $pdo = $this->databaseConnection->getPDOInstance();
            $stmt = $pdo->prepare("SELECT * FROM $tableName");
            $stmt->execute();
            $this->config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
            $this->logger->error("Errore nel caricamento della configurazione dal database: " . $e->getMessage());
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
        return $this->config[$key] ?? $default;
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