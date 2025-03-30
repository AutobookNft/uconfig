<?php

namespace Ultra\UltraConfigManager;

class Logger
{
    public function error(string $message, array $context = []): void
    {
        // Per ora, semplicemente scriviamo l'errore su stderr
        // In futuro, potremmo integrare Monolog o un altro sistema di logging più robusto
        $contextString = !empty($context) ? json_encode($context) : '';
        fwrite(STDERR, date('Y-m-d H:i:s') . " ERROR: $message $contextString\n");
    }
} 