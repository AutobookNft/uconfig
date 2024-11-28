<?php

namespace UltraProject\UConfig\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use UltraProject\UConfig\Models\UConfig;

class UConfigController extends Controller
{
    public function index()
    {
        $configs = UConfig::all();
        return view('uconfig::index', compact('configs'));
    }

    public function create()
    {
        return view('uconfig::create');
    }

    public function store(Request $request)
    {
        // Valida e salva la configurazione
    }

    public function edit($key)
    {
        $config = UConfig::findOrFail($key);
        return view('uconfig::edit', compact('config'));
    }

    public function update(Request $request, $key)
    {
        // Valida e aggiorna la configurazione
    }

    public function destroy($key)
    {
        $config = UConfig::findOrFail($key);
        $config->delete();
        return redirect()->route('uconfig.index')->with('success', 'Configurazione eliminata con successo.');
    }
} 