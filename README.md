# UConfig

UConfig è una libreria PHP moderna e flessibile per la gestione delle configurazioni, che permette di caricare e gestire configurazioni sia da file che da database in modo semplice ed efficiente.

## Caratteristiche Principali

- 🔄 Caricamento configurazioni da file PHP e database
- 🔒 Gestione sicura delle variabili d'ambiente
- 📝 Sistema di logging integrato
- 🛠 Facile integrazione con Laravel tramite Service Provider
- 🎯 Design Pattern Singleton per la connessione al database
- ⚡ Performance ottimizzate con caricamento lazy

## Requisiti

- PHP >= 8.1
- PDO Extension
- Composer

## Installazione

1. Installare il pacchetto tramite Composer:

   ```bash
   composer require fabiocherici/uconfig
   ```

2. Pubblicare il file di configurazione e la migration:

   ```bash
   php artisan vendor:publish --provider="Fabio\UConfig\Providers\UConfigServiceProvider" --tag="config"
   php artisan vendor:publish --provider="Fabio\UConfig\Providers\UConfigServiceProvider" --tag="migrations"
   ```

3. Eseguire le migrazioni:

   ```bash
   php artisan migrate
   ```

```bash
composer require fabiocherici/uconfig
```

## Configurazione

### 