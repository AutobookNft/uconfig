<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurazioni di UConfig
    |--------------------------------------------------------------------------
    */
    
    // Configurazione del database
    'database' => [
        'table' => 'uconfig',  // nome della tabella per le configurazioni
    ],
    
    // Altre impostazioni
    'cache' => [
        'enabled' => true,
        'ttl' => 3600  // tempo in secondi
    ],

    /**
     * Configurazione: roles_permissions
     * 
     * Questa configurazione definisce il driver di gestione dei permessi da utilizzare
     * all'interno di UCM (UltraConfigManager).
     * 
     * 'driver' => Specifica il sistema di gestione dei permessi scelto. I valori possibili sono:
     *   - 'jetstream': Usa il sistema di gestione dei permessi integrato in Jetstream, basato sui team.
     *   - 'spatie': Usa la libreria Spatie Permission per una gestione avanzata di ruoli e permessi.
     *   - 'gates': Usa i Gate e le Policy nativi di Laravel per un controllo personalizzato.
     *   - 'none': Nessun sistema di permessi viene utilizzato. UCM funzionerà senza restrizioni 
     *             basate su ruoli o permessi.
     * 
     * Scopo:
     * - Consentire a UCM di adattarsi dinamicamente al sistema di permessi configurato
     *   nell'applicazione ospitante.
     * 
     * Nota:
     * - Il valore predefinito è 'jetstream'. Se 'none' viene selezionato, UCM ignora
     *   ogni controllo relativo ai permessi e ruoli.
     */

    'roles_permissions' => [
        'driver' => env('ROLE_MANAGER_DRIVER', 'jetstream'), // Possibili valori: jetstream, spatie, gates
    ],
];  
