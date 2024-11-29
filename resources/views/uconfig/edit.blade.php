@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifica Configurazione</h1>
    <form action="{{ route('uconfig.update', $config->key) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block font-medium mb-1">Chiave</label>
            <input type="text" name="key" value="{{ $config->key }}" class="w-full px-3 py-2 border rounded-md" readonly>
        </div>
        <div class="mb-4">
            <label class="block font-medium mb-1">Valore</label>
            <textarea name="value" rows="4" class="w-full px-3 py-2 border rounded-md" required>{{ $config->value }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block font-medium mb-1">Categoria</label>
            <input type="text" name="category" value="{{ $config->category }}" class="w-full px-3 py-2 border rounded-md">
        </div>
        <div class="mb-4">
            <label class="block font-medium mb-1">Note</label>
            <textarea name="note" rows="2" class="w-full px-3 py-2 border rounded-md">{{ $config->note }}</textarea>
        </div>
        <div class="flex justify-end">
            <a href="{{ route('uconfig.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md mr-2">Annulla</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Aggiorna</button>
        </div>
    </form>
</div>
@endsection
