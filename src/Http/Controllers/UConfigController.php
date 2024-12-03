<?php

namespace UltraProject\UConfig\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    protected $uconfig;

    /**
     * Constructor.
     *
     * @param UConfigService $uconfig
     */
    public function __construct(UConfigService $uconfig)
    {
        $this->uconfig = $uconfig;
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

    public function store(Request $request, $userId = null)
    {
        Log::info('store: newValue: request->key:' . $request->key);

        $data = $request->validate([
            'key' => 'required|unique:uconfig,key',
            'value' => 'required',
            'category' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($data, $userId) {
                tap(UConfig::create($data), function ($config) use ($userId) {
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
                        'user_id' => $userId,
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

        try {
            DB::transaction(function () use ($config, $data, $oldValue, $userId) {
                tap($config, function ($config) use ($data, $oldValue, $userId) {
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
                        'user_id' => $userId,
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

    public function destroy($id, $userId = null)
    {
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
    