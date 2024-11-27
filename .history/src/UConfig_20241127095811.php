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
        } elseif (strpos($source, '.php') !== false) {
            $this->loadFromFile($source);
        } else {
            $this->loadFromDatabase($source, $options);
        }
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    private function loadFromFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            $this->logger->error("File di configurazione non trovato: $filePath");
            return;
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
} 