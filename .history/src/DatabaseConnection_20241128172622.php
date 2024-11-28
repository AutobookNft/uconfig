<?php

namespace Fabio\UConfig;

use PDO;

class DatabaseConnection
{
    private static $instance = null;
    private $pdo;

    private function __construct(EnvLoader $envLoader)
    {
        $host = $envLoader->get('DB_HOST');
        $dbname = $envLoader->get('DB_DATABASE');
        $user = $envLoader->get('DB_USERNAME');
        $password = $envLoader->get('DB_PASSWORD');

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance(EnvLoader $envLoader): self
    {
        if (self::$instance === null) {
            self::$instance = new self($envLoader);
        }
        return self::$instance;
    }

    public function getPDOInstance(): PDO
    {
        return $this->pdo;
    }
} 