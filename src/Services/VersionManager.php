<?php

namespace UltraProject\UConfig\Services;

use UltraProject\UConfig\Models\UConfigVersion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class VersionManager
{
    /**
     * Ottiene la prossima versione di una configurazione.
     *
     * @param int $configId L'ID della configurazione.
     * @return int La versione incrementata di uno.
     * @throws \Exception Se si verifica un errore durante il calcolo.
     */
    public function getNextVersion(int $configId): int
    {
        try {
            // Valida che l'ID sia positivo
            if ($configId <= 0) {
                throw new \InvalidArgumentException("L'ID della configurazione deve essere un numero positivo.");
            }

            // Ottiene la versione più alta per la configurazione specifica
            $latestVersion = UConfigVersion::where('uconfig_id', $configId)->max('version');

            // Incrementa di uno (ritorna 1 se non ci sono versioni esistenti)
            return $latestVersion ? $latestVersion + 1 : 1;

        } catch (QueryException $e) {
            // Logga l'errore del database
            Log::error("Errore nel calcolo della versione per config_id {$configId}: " . $e->getMessage());
            throw new \Exception("Errore durante il calcolo della versione. Riprovare più tardi.");
        } catch (\Exception $e) {
            // Logga altri errori generici
            Log::error("Errore generico nel calcolo della versione: " . $e->getMessage());
            throw $e; // Rilancia l'eccezione per un'ulteriore gestione a monte
        }
    }
}
