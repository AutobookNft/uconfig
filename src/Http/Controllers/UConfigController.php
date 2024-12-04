<?php

namespace UltraProject\UConfig\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use UltraProject\UConfig\Constants\GlobalConstants;
use UltraProject\UConfig\Models\UConfig;
use UltraProject\UConfig\Models\UConfigVersion;
use UltraProject\UConfig\Models\UConfigAudit;
use UltraProject\UConfig\UConfig as UConfigService;


class UConfigController extends Controller
{
    /**
     * UConfig service instance.
     *
     * @var UConfigService
     */
    protected UConfigService $uconfig;
    protected GlobalConstants $globalConstants;

    /**
     * Constructor.
     *
     * @param UConfigService $uconfig
     */
    public function __construct(UConfigService $uconfig, GlobalConstants $globalConstants)
    {
        $this->uconfig = $uconfig;
        $this->globalConstants = $globalConstants;
    }

    public function index()
    {
        $configs = UConfig::all();
        return view('vendor.uconfig.index', compact('configs'));
    }

    public function create()
    {
        return view('vendor.uconfig.create');
    }

    public function store(Request $request)
    {

        $user = $request->user();
        
        
         // Controllo permesso per creare una configurazione
        if ($user && !UConfig::permissions()->can($user, 'create-config')) {
            abort(403, "Accesso negato: l'utente non ha i permessi per creare configurazioni.");
        }
        
        Log::info('store: newValue: request->key:' . $request->key);

        $data = $request->validate([
            'key' => 'required|unique:uconfig,key',
            'value' => 'required',
            'category' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($data, $user) {
                tap(UConfig::create($data), function ($config) use ($user) {
                    if (!$config || !$config->id) {
                        Log::error('Errore nella creazione di UConfig');
                        throw new \Exception('Errore nella creazione di UConfig');
                    }

                    UConfigVersion::create([
                        'uconfig_id' => $config->id,
                        'version' => 1,
                        'key' => $config->key,
                        'category' => $config->category,
                        'note' => $config->note,
                        'value' => $config->value,
                    ]);

                    UConfigAudit::create([
                        'uconfig_id' => $config->id,
                        'action' => 'created',
                        'new_value' => $config->value,
                        'user_id' => $user?->id ?? $this->globalConstants::NO_USER, // NO_USER se $user è null
                    ]);
                });
            });

            $this->uconfig->refreshConfigCache();

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

    public function update(Request $request, $id, )
    {

        $user = $request->user();
        
        // Controllo permesso per aggiornare una configurazione
        if ($user && !UConfig::permissions()->can($user, 'update-config')) {
            abort(403, "Accesso negato: l'utente non ha i permessi per aggiornare configurazioni.");
        }
        
        
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

        try {
            DB::transaction(function () use ($config, $data, $oldValue, $user) {
                tap($config, function ($config) use ($data, $oldValue, $user) {
                    $config->update($data);

                    $latestVersion = UConfigVersion::where('uconfig_id', $config->id)->max('version');
                    UConfigVersion::create([
                        'uconfig_id' => $config->id,
                        'version' => $latestVersion + 1,
                        'key' => $config->key,
                        'category' => $config->category,
                        'note' => $config->note,
                        'value' => $config->value,
                    ]);

                    UConfigAudit::create([
                        'uconfig_id' => $config->id,
                        'action' => 'updated',
                        'old_value' => $oldValue,
                        'new_value' => $config->value,
                        'user_id' => $user?->id ?? $this->globalConstants::NO_USER, // NO_USER se $user è null
                    ]);
                });
            });

            $this->uconfig->refreshConfigCache();

            return redirect()->route('uconfig.index')->with('success', 'Configurazione aggiornata con successo.');
        } catch (\Exception $e) {
            Log::error('Errore durante l\'aggiornamento della configurazione: ' . $e->getMessage());
            return redirect()->route('uconfig.index')->with('error', 'Errore durante l\'aggiornamento della configurazione.');
        }
    }

    public function destroy(Request $request, $id)
    {
                
        $user = $request->user();

        $userId = $user ? $user->id : 0 ; // 0 se non loggato

        // Controllo permesso per eliminare una configurazione
        if ($user && !UConfig::permissions()->can($user, 'delete-config')) {
            abort(403, "Accesso negato: l'utente non ha i permessi per eliminare configurazioni.");
        }
        
        
        $config = UConfig::findOrFail($id);
        $oldValue = $config->value;

        UConfigAudit::create([
            'uconfig_id' => $config->id,
            'action' => 'deleted',
            'old_value' => $oldValue,
            'user_id' => $userId,
        ]);

        $config->delete();

        $this->uconfig->refreshConfigCache();

        return redirect()->route('uconfig.index')->with('success', 'Configurazione eliminata con successo.');
    }

    public function audit($id)
    {
        $config = UConfig::findOrFail($id);
        $audits = UConfigAudit::where('uconfig_id', $id)->get();

        return view('vendor.uconfig.audit', compact('config', 'audits'));
    }
}
    