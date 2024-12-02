<?php

namespace UltraProject\UConfig;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use UltraProject\UConfig\Models\UConfig as UConfigModel;
use UltraProject\UConfig\Models\UConfigVersion;
use UltraProject\UConfig\Models\UConfigAudit;

/**
 * UConfig - Centralized configuration management class.
 */
class UConfig
{
    /**
     * In-memory configuration array.
     *
     * @var array
     */
    private $config = [];

    /**
     * EnvLoader instance for .env variables.
     *
     * @var EnvLoader
     */
    private $envLoader;

    /**
     * Cache key used to store the configurations.
     *
     * @var string
     */
    private const CACHE_KEY = 'uconfig.cache';

    /**
     * Constructor.
     *
     * @param EnvLoader $envLoader Instance to load environment variables.
     */
    public function __construct(EnvLoader $envLoader)
    {
        $this->envLoader = $envLoader;
        $this->loadConfig(); // Load configuration on initialization
    }

    /**
     * Load all configurations into memory from cache, database, and .env file.
     */
    public function loadConfig(): void
    {
        // Attempt to load from cache
        $this->config = Cache::remember(self::CACHE_KEY, 3600, function () {
            return $this->loadFromDatabase();
        });

        // Merge with .env variables
        $this->loadFromEnv();

        Log::info('Configurations successfully loaded into memory.');
    }

    /**
     * Load configurations from the database.
     *
     * @return array Configuration data from the database.
     */
    private function loadFromDatabase(): array
    {
        $configArray = [];
        $configs = UConfigModel::all();

        foreach ($configs as $config) {
            try {
                // Ensure the value is not null before decrypting
                if ($config->value !== null) {
                    $decryptedValue = Crypt::decryptString($config->value);
                    $configArray[$config->key] = [
                        'value' => $decryptedValue,
                        'category' => $config->category,
                    ];
                } else {
                    Log::warning("Configuration with key {$config->key} has a null value and will be ignored.");
                }
            } catch (\Exception $e) {
                Log::error("Error decrypting value for key {$config->key}: " . $e->getMessage());
            }
        }

        return $configArray;
    }

    /**
     * Load configurations from the .env file and merge them with existing configurations.
     */
    private function loadFromEnv(): void
    {
        $envConfig = $this->envLoader->all();

        foreach ($envConfig as $key => $value) {
            if (!array_key_exists($key, $this->config)) {
                $this->config[$key] = ['value' => $value];
            }
        }
    }

    /**
     * Retrieve a configuration value.
     *
     * @param string $key Configuration key.
     * @param mixed $default Default value if key does not exist.
     * @return mixed Configuration value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key]['value'] ?? $default;
    }

    /**
     * Set a configuration value and persist it to the database.
     *
     * @param string $key Configuration key.
     * @param mixed $value Configuration value.
     * @param string|null $category Configuration category (optional).
     */
    public function set(string $key, mixed $value, ?string $category = null): void
    {
        // Update in-memory configuration
        $this->config[$key] = [
            'value' => $value,
            'category' => $category,
        ];

        // Save to database
        $config = $this->saveToDatabase($key, $value, $category);

        if ($config) {
            $this->saveVersion($config, $value);
            $this->saveAudit($config, 'updated', $value);
        }

        // Update cache
        Cache::put(self::CACHE_KEY, $this->config, 3600);
    }

    /**
     * Save a configuration to the database.
     *
     * @param string $key Configuration key.
     * @param mixed $value Configuration value.
     * @param string|null $category Configuration category.
     * @return UConfigModel|null Saved UConfigModel instance or null on failure.
     */
    private function saveToDatabase(string $key, mixed $value, ?string $category): ?UConfigModel
    {
        try {
            $encryptedValue = Crypt::encryptString(is_scalar($value) ? $value : json_encode($value));
            $config = UConfigModel::updateOrCreate(
                ['key' => $key],
                ['value' => $encryptedValue, 'category' => $category]
            );
            Log::info("Configuration saved to database: $key");
            return $config;
        } catch (\Exception $e) {
            Log::error("Error saving configuration $key to database: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Save a new version for a configuration.
     *
     * @param UConfigModel $config UConfigModel instance.
     * @param mixed $value Configuration value.
     */
    private function saveVersion(UConfigModel $config, mixed $value): void
    {
        try {
            $latestVersion = UConfigVersion::where('uconfig_id', $config->id)->max('version') ?? 0;
            $encryptedValue = Crypt::encryptString(is_scalar($value) ? $value : json_encode($value));

            UConfigVersion::create([
                'uconfig_id' => $config->id,
                'version' => $latestVersion + 1,
                'key' => $config->key,
                'category' => $config->category,
                'value' => $encryptedValue,
            ]);

            Log::info("Version registered for configuration: {$config->key}");
        } catch (\Exception $e) {
            Log::error("Error registering version for configuration {$config->key}: " . $e->getMessage());
        }
    }

    /**
     * Save an audit log for a configuration.
     *
     * @param UConfigModel $config UConfigModel instance.
     * @param string $action Action type (e.g., 'created', 'updated', 'deleted').
     * @param mixed $newValue New configuration value.
     */
    private function saveAudit(UConfigModel $config, string $action, mixed $newValue): void
    {
        try {
            $oldValue = $this->get($config->key);
            $encryptedNewValue = Crypt::encryptString(is_scalar($newValue) ? $newValue : json_encode($newValue));
            $encryptedOldValue = $oldValue ? Crypt::encryptString($oldValue) : null;

            UConfigAudit::create([
                'uconfig_id' => $config->id,
                'action' => $action,
                'new_value' => $encryptedNewValue,
                'old_value' => $encryptedOldValue,
                'user_id' => Auth::id(),
            ]);

            Log::info("Audit registered for action $action on configuration: {$config->key}");
        } catch (\Exception $e) {
            Log::error("Error registering audit for configuration {$config->key}: " . $e->getMessage());
        }
    }

    /**
     * Delete a configuration from memory and database.
     *
     * @param string $key Configuration key.
     */
    public function delete(string $key): void
    {
        unset($this->config[$key]);

        $config = UConfigModel::where('key', $key)->first();
        if ($config) {
            try {
                $config->delete();
                $this->saveAudit($config, 'deleted', null);
                Log::info("Configuration deleted: $key");
            } catch (\Exception $e) {
                Log::error("Error deleting configuration $key: " . $e->getMessage());
            }
        }

        // Update cache
        Cache::put(self::CACHE_KEY, $this->config, 3600);
    }

    /**
     * Retrieve all configurations.
     *
     * @return array All configuration values.
     */
    public function all(): array
    {
        return array_map(fn($config) => $config['value'], $this->config);
    }
}
