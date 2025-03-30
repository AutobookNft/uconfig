<?php

namespace Ultra\UltraConfigManager;

class EnvLoader
{
    private $env = [];

    public function __construct()
    {
        $this->loadEnv();
    }

    private function loadEnv(): void
    {
        $envFile = __DIR__ . '/../../../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    // Ignora i commenti
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $this->env[trim($key)] = trim($value);
                }
            }
        }
        // Carica anche le variabili d'ambiente presenti in $_ENV
        $this->env = array_merge($this->env, $_ENV);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->env[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->env;
    }
} 