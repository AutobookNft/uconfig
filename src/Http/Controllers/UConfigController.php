<?php

namespace UltraProject\UConfig\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use UltraProject\UConfig\Models\UConfig;
use UltraProject\UConfig\Models\UConfigVersion;
use UltraProject\UConfig\Models\UConfigAudit;

class UConfigController extends Controller
{
    public function index()
    {
        $configs = UConfig::all();
        return view('vendor.uconfig.index', compact('configs'));
    }

    public function create()
    {
        return view('vendor.uconfig.create');
    }

    public function store(Request $request, $userId = null)
    {
        // Log::info('store: newValue: request:' . $request);

        Log::info('store: newValue: request->key:' . $request->key);

        $data = $request->validate([
            'key' => 'required|unique:uconfig,key',  // Specifica la tabella e la colonna per la verifica di unicità
            'value' => 'required',
            'category' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($data, $userId) {

                tap(UConfig::create($data), function ($config) use ($userId) {

                    Log::info('store: newValue: config->id:' . $config->id);

                    // Assicurati che $config sia stato creato correttamente
                    if (!$config || !$config->id) {
                        log::error('Errore nella creazione di UConfig');
                        throw new \Exception('Errore nella creazione di UConfig');
                    }

                    // Creazione della versione di configurazione
                    try {
                        UConfigVersion::create([
                            'uconfig_id' => $config->id,
                            'version' => 1, // La prima versione
                            'key' => $config->key,
                            'category' => $config->category,
                            'note' => $config->note,
                            'value' => $config->value,
                        ]);
                    } catch (\Exception $e) {
                        log::error('Errore nella creazione di UConfigVersion: ' . $e->getMessage());
                        throw new \Exception('Errore nella creazione di UConfigVersion: ' . $e->getMessage());
                    }


                    // Creazione dell'audit per la configurazione
                    try {
                        UConfigAudit::create([
                            'uconfig_id' => $config->id,
                            'action' => 'created',
                            'new_value' => $config->value,
                            'user_id' => $userId,
                        ]);
                    } catch (\Exception $e) {
                        log::error('Errore nella creazione di UConfigAudit: ' . $e->getMessage());
                        throw new \Exception('Errore nella creazione di UConfigAudit: ' . $e->getMessage());
                    }

                });
            });


            return redirect()->route('uconfig.index')->with('success', 'Configurazione aggiunta con successo.');
        } catch (\Exception $e) {
            return redirect()->route('uconfig.index')->with('error', 'Errore durante l\'aggiunta della configurazione: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $config = UConfig::findOrFail($id);
        $audits = UConfigAudit::where('uconfig_id', $id)->get();

        return view('vendor.uconfig.edit', compact('config', 'audits'));
    }

    public function update(Request $request, $id, $userId = null)
    {
        $config = UConfig::findOrFail($id);

        $data = $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
            'category' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $oldValue = $config->value;

        Log::info('oldValue: ' . $oldValue);
        Log::info('newValue: ' . $data['value']);

        // Gestione degli errori con try-catch
        try {
            DB::transaction(function () use ($config, $data, $oldValue, $userId) {
                tap($config, function ($config) use ($data, $oldValue, $userId) {
                    // Aggiornamento della configurazione
                    $config->update($data);

                    // Crea una nuova versione della configurazione
                    $latestVersion = UConfigVersion::where('uconfig_id', $config->id)->max('version');
                    UConfigVersion::create([
                        'uconfig_id' => $config->id,
                        'version' => $latestVersion + 1,
                        'key' => $config->key,
                        'category' => $config->category,
                        'note' => $config->note,
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
                });
            });

            return redirect()->route('uconfig.index')->with('success', 'Configurazione aggiornata con successo.');

        } catch (\Exception $e) {
            // Log dell'errore per il debugging
            Log::error('Errore durante l\'aggiornamento della configurazione: ' . $e->getMessage());

            // Redirige con un messaggio di errore
            return redirect()->route('uconfig.index')->with('error', 'Errore durante l\'aggiornamento della configurazione. Si prega di riprovare.');
        }
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

    public function audit($id)
    {
        $config = UConfig::findOrFail($id);
        $audits = UConfigAudit::where('uconfig_id', $id)->get();

        return view('vendor.uconfig.audit', compact('config', 'audits'));
    }
    
}