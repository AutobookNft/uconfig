<?php

namespace UltraProject\UConfig\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use UltraProject\UConfig\Models\UConfig;
use UltraProject\UConfig\Models\UConfigVersion;
use UltraProject\UConfig\Models\UConfigAudit;

class UConfigController extends Controller
{
    public function index()
    {
        $configs = UConfig::all();
        return view('uconfig.index', compact('configs'));
    }

    public function create()
    {
        return view('uconfig.create');
    }

    public function store(Request $request, $userId = null)
    {
        $data = $request->validate([
            'key' => 'required|unique:uconfig,key',
            'value' => 'required',
            'category' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $config = UConfig::create($data);

        if ($config) {
            UConfigVersion::create([
                'uconfig_id' => $config->id,
                'version' => 1,
                'value' => $config->value,
            ]);

            UConfigAudit::create([
                'uconfig_id' => $config->id,
                'action' => 'created',
                'new_value' => $config->value,
                'user_id' => $userId,
            ]);

            return redirect()->route('uconfig.index')->with('success', 'Configurazione aggiunta con successo.');
        }

        return redirect()->route('uconfig.index')->with('error', 'Errore durante la creazione della configurazione.');
    }

    public function edit($id)
    {
        $config = UConfig::findOrFail($id);
        return view('uconfig.edit', compact('config'));
    }

    public function update(Request $request, $id, $userId = null)
    {
        $config = UConfig::findOrFail($id);

        $data = $request->validate([
            'value' => 'required',
            'category' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $oldValue = $config->value;
        $config->update($data);

        // Crea una nuova versione
        $latestVersion = UConfigVersion::where('uconfig_id', $config->id)->max('version');
        UConfigVersion::create([
            'uconfig_id' => $config->id,
            'version' => $latestVersion + 1,
            'value' => $config->value,
        ]);

        // Registra l'audit
        UConfigAudit::create([
            'uconfig_id' => $config->id,
            'action' => 'updated',
            'old_value' => $oldValue,
            'new_value' => $config->value,
            'user_id' => $userId,
        ]);

        return redirect()->route('uconfig.index')->with('success', 'Configurazione aggiornata con successo.');
    }

    public function destroy($id, $userId = null)
    {
        $config = UConfig::findOrFail($id);
        $oldValue = $config->value;

        // Registra l'audit
        UConfigAudit::create([
            'uconfig_id' => $config->id,
            'action' => 'deleted',
            'old_value' => $oldValue,
            'user_id' => $userId,
        ]);

        $config->delete();

        return redirect()->route('uconfig.index')->with('success', 'Configurazione eliminata con successo.');
    }
}