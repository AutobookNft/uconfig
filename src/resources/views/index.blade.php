<x-app-layout>
    @include('vendor.uconfig._internal_navbar')
    <div class="container mx-auto py-10">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Gestione Configurazioni</h2>
            <a href="{{ route('uconfig.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md">Aggiungi Configurazione</a>
        </div>
        <div class="overflow-hidden rounded-lg shadow-md">
            <table class="min-w-full bg-white border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-100 to-gray-200 text-left text-sm uppercase tracking-wider text-gray-600">
                        <th class="py-4 px-6 border-b font-semibold">Chiave</th>
                        <th class="py-4 px-6 border-b font-semibold">Valore Attuale</th>
                        <th class="py-4 px-6 border-b font-semibold">Categoria</th>
                        <th class="py-4 px-6 border-b font-semibold">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($configs as $config)
                    <tr class="hover:bg-gray-50 transition duration-200">
                        <td class="py-4 px-6 border-b text-gray-800">{{ $config->key }}</td>
                        <td class="py-4 px-6 border-b text-gray-600">{{ $config->value }}</td>
                        <td class="py-4 px-6 border-b text-gray-600">{{ $config->category }}</td>
                        <td class="py-4 px-6 border-b">
                            <div class="flex space-x-4">
                                <a href="{{ route('uconfig.edit', $config->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow">Modifica</a>
                                <a href="{{ route('uconfig.audit', $config->id) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-md shadow">Visualizza Audit</a>
                                <form action="{{ route('uconfig.destroy', $config->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md shadow" onclick="return confirm('Sei sicuro di voler eliminare questa configurazione?')">Elimina</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('vendor.uconfig.footer')
</x-app-layout>
