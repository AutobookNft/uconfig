<?php

namespace Fabio\UConfig\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class UConfigServiceProvider extends ServiceProvider implements DeferrableProvider
{
    // ... codice esistente ...

    /**
     * Determina se il provider è "differibile".
     *
     * @return bool
     */
    public function isDeferred()
    {
        return true;
    }
}