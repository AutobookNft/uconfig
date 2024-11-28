<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ConfigManagerMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $hasRole = false;

        // Verifica se l'opzione 'use_spatie_permissions' è attiva
        if (config('uconfig.use_spatie_permissions') && method_exists($user, 'hasRole')) {
            // Usa il metodo di Spatie
            $hasRole = $user->hasRole('ConfigManager');
        } else {
            // Usa un controllo personalizzato
            $hasRole = $user->role === 'ConfigManager';
        }

        if (!$hasRole) {
            abort(403, 'Non hai i permessi per accedere a questa sezione.');
        }

        return $next($request);
    }
} 