<?php

namespace UltraProject\UConfig;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use UltraProject\UConfig\Models\UConfig as UConfigModel;
use UltraProject\UConfig\Models\UConfigVersion;
use UltraProject\UConfig\Models\UConfigAudit;
use UltraProject\UConfig\Permissions\PermissionManager;

/**
 * UConfig - Centralized configuration management class.
 */
class UConfig
{
    /**
     * Summary of permissionManager
     * @var 
     */
    protected ?PermissionManager $permissionManager = null;

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
        $this->permissionManager = new PermissionManager();
    }

    public function permissions(): PermissionManager
    {
        return $this->permissionManager;
    }  

    /**
     * Load all configurations into memory from cache, database, and .env file.
     */
    public function loadConfig(): void
    {

        Log::info('UConfig loadConfig');

        // Check if cache is enabled
        $use_cache = config('uconfig.cache.enabled');

        // Attempt to load from cache
        if ($use_cache) {
            $ttl= config('uconfig.cache.ttl');
            $this->config = Cache::remember(self::CACHE_KEY, $ttl, function () {
                $this->loadFromDatabase();
                $this->loadFromEnv();
            });
            Log::info('Cache enabled');
        } else {
            Log::info('Cache disabled');
            $this->loadFromDatabase();
            $this->loadFromEnv();
        }

        return;
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
                // Assicurati che il valore non sia nullo
                if ($config->value !== null) {
                    $configArray[$config->key] = [
                        'value' => $config->value, // Decrittografia gestita dal cast
                        'category' => $config->category,
                    ];
                } else {
                    Log::warning("Configuration with key {$config->key} has a null value and will be ignored.");
                }
            } catch (\Exception $e) {
                Log::error("Error processing configuration with key {$config->key}: " . $e->getMessage());
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
    public function get(string $key, mixed $default = null, $user = null): mixed
    {

        // Controlla i permessi prima di leggere i dati
        if ($user && !$this->permissions()->can($user, 'read-config')) {
            throw new \Exception("Accesso negato: l'utente non ha i permessi per leggere la configurazione.");
        }
            
        // Se la cache non è sincronizzata, ricarica in memoria
        if (empty($this->config)) {
            $this->config = Cache::get(self::CACHE_KEY, []);
        }

        // Log::info("get key: $key" . json_encode($this->config));
        return $this->config[$key]['value'] ?? $default;

    }

    /**
     * Set a configuration value and persist it to the database.
     *
     * @param string $key Configuration key.
     * @param mixed $value Configuration value.
     * @param string|null $category Configuration category (optional).
     */
    public function set(string $key, mixed $value, ?string $category = null, $user = null): void
    {
        
        // Controlla i permessi prima di modificare i dati
        if ($user && !$this->permissions()->can($user, 'set-config')) {
            throw new \Exception("Accesso negato: l'utente non ha i permessi per modificare la configurazione.");
        }
        
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
        $this->refreshConfigCache();

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
            // Verifica se il record esiste, inclusi quelli eliminati
            $config = UConfigModel::withTrashed()->where('key', $key)->first();

            if ($config) {
                // Se il record è eliminato, lo ripristina
                if ($config->trashed()) {
                    $config->restore();
                }

                // Aggiorna i dati del record
                $config->update([
                    'value' => $value,
                    'category' => $category,
                ]);
            } else {
                // Se il record non esiste, lo crea
                $config = UConfigModel::create([
                    'key' => $key,
                    'value' => $value,
                    'category' => $category,
                ]);
            }

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
            // Recupera l'ultima versione registrata o usa 0 come default
            $latestVersion = UConfigVersion::where('uconfig_id', $config->id)->max('version') ?? 0;

            // Salvataggio diretto; il cast si occuperà della crittografia
            UConfigVersion::create([
                'uconfig_id' => $config->id,
                'version' => $latestVersion + 1,
                'key' => $config->key,
                'category' => $config->category,
                'value' => $value, // Passaggio diretto; crittografia gestita dal cast
            ]);

            // Log::info("Version registered for configuration: {$config->key}");
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
            // Recupera il valore precedente
            $oldValue = $this->get($config->key);

            // Salvataggio diretto; il cast nel modello si occuperà della crittografia
            UConfigAudit::create([
                'uconfig_id' => $config->id,
                'action' => $action,
                'new_value' => $newValue, // Critografia gestita dal cast
                'old_value' => $oldValue, // Critografia gestita dal cast
                'user_id' => Auth::id(),
            ]);

            // Log::info("Audit registered for action $action on configuration: {$config->key}");
        } catch (\Exception $e) {
            Log::error("Error registering audit for configuration {$config->key}: " . $e->getMessage());
        }
    }

    /**
     * Delete a configuration from memory and database.
     *
     * @param string $key Configuration key.
     */
    public function delete(string $key, $user = null): void
    {
        
        if ($user && !$this->permissions()->can($user, 'delete-config')) {
            throw new \Exception("Accesso negato: l'utente non ha i permessi per eliminare la configurazione.");
        }
        
        unset($this->config[$key]);
        Log::info("get key: $key" . json_encode($this->config));

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
        $this->refreshConfigCache();
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

    /**
     * Update the cache with the current in-memory configurations.
     */
    public function refreshConfigCache(): void
    {
        try {
            // Ricarica tutte le configurazioni dal database
            $this->config = $this->loadFromDatabase();

            // Aggiorna la cache con i dati attuali
            Cache::put(self::CACHE_KEY, $this->config, 3600);

            Log::info('Cache refreshed successfully.');
        } catch (\Exception $e) {
            Log::error('Error refreshing the cache: ' . $e->getMessage());
        }
    }

}
