<?php

namespace Fabio\UConfig;

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
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $this->env[trim($key)] = trim($value);
                }
            }
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $this->env[$key] ?? $default;
    }
} 