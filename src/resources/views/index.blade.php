@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Gestione Configurazioni</h1>
    <a href="{{ route('vendor.uconfig.create') }}" class="mb-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Aggiungi Configurazione</a>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead class="bg-gray-200">
                <tr>
                    <th class="py-2 px-4 border-b">Chiave</th>
                    <th class="py-2 px-4 border-b">Categoria</th>
                    <th class="py-2 px-4 border-b">Valore</th>
                    <th class="py-2 px-4 border-b">Note</th>
                    <th class="py-2 px-4 border-b">Azioni</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($configs as $config)
                    <tr class="border-b hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $config->key }}</td>
                        <td class="py-2 px-4">{{ $config->category }}</td>
                        <td class="py-2 px-4">{{ $config->value }}</td>
                        <td class="py-2 px-4">{{ $config->note }}</td>
                        <td class="py-2 px-4">
                            <a href="{{ route('vendor.uconfig.edit', $config->id) }}" class="text-yellow-600 hover:underline">Modifica</a>
                            <form action="{{ route('vendor.uconfig.destroy', $config->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline ml-2">Elimina</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
