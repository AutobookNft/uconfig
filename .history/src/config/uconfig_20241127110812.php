<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurazioni di UConfig
    |--------------------------------------------------------------------------
    */
    
    // Configurazione del database
    'database' => [
        'table' => 'configurations',  // nome della tabella per le configurazioni
    ],
    
    // Altre impostazioni
    'cache' => [
        'enabled' => true,
        'ttl' => 3600  // tempo in secondi
    ],
]; 