<x-app-layout>
    @include('vendor.uconfig._internal_navbar')
    <div class="container mx-auto py-10">
        <h2 class="text-3xl font-bold mb-6 text-gray-800">Modifica Configurazione</h2>
        <div class="bg-white p-8 rounded-lg shadow-md">
            <form method="POST" action="{{ route('uconfig.update', $config->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="key" class="block text-lg font-semibold text-gray-700 mb-2">Chiave</label>
                    <input type="text" id="key" name="key" class="form-input mt-1 block w-full bg-gray-200 rounded-md border border-gray-300" value="{{ $config->key }}" readonly>
                </div>

                <div class="mb-6">
                    <label for="value" class="block text-lg font-semibold text-gray-700 mb-2">Valore</label>
                    <input type="text" id="value" name="value" class="form-input mt-1 block w-full rounded-md border border-gray-300" value="{{ $config->value }}">
                </div>

                <div class="mb-6">
                    <label for="category" class="block text-lg font-semibold text-gray-700 mb-2">Categoria</label>
                    <input type="text" id="category" name="category" class="form-input mt-1 block w-full rounded-md border border-gray-300" value="{{ $config->category }}">
                </div>

                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg shadow-md">Aggiorna</button>
            </form>
        </div>

        <h3 class="text-2xl font-bold mt-10 mb-6 text-gray-800">Storico Modifiche</h3>
        <div class="overflow-hidden rounded-lg shadow-md">
            <table class="min-w-full bg-white border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-100 to-gray-200 text-left text-sm uppercase tracking-wider text-gray-600">
                        <th class="py-4 px-6 border-b font-semibold">Data Modifica</th>
                        <th class="py-4 px-6 border-b font-semibold">Vecchio Valore</th>
                        <th class="py-4 px-6 border-b font-semibold">Nuovo Valore</th>
                        <th class="py-4 px-6 border-b font-semibold">Azione Effettuata</th>
                        <th class="py-4 px-6 border-b font-semibold">Utente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($audits as $audit)
                    <tr class="hover:bg-gray-50 transition duration-200">
                        <td class="py-4 px-6 border-b text-gray-800">{{ $audit->created_at }}</td>
                        <td class="py-4 px-6 border-b text-gray-600">{{ $audit->old_value }}</td>
                        <td class="py-4 px-6 border-b text-gray-600">{{ $audit->new_value }}</td>
                        <td class="py-4 px-6 border-b text-gray-600">{{ $audit->action }}</td>
                        <td class="py-4 px-6 border-b text-gray-800">{{ $audit->user ? $audit->user->name : 'Utente sconosciuto' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('vendor.uconfig.footer')
</x-app-layout>
