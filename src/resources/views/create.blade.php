@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Aggiungi Configurazione</h1>
    <form action="{{ route('uconfig.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block font-medium mb-1">Chiave</label>
            <input type="text" name="key" class="w-full px-3 py-2 border rounded-md" required>
        </div>
        <div class="mb-4">
            <label class="block font-medium mb-1">Valore</label>
            <textarea name="value" rows="4" class="w-full px-3 py-2 border rounded-md" required></textarea>
        </div>
        <div class="mb-4">
            <label class="block font-medium mb-1">Categoria</label>
            <input type="text" name="category" class="w-full px-3 py-2 border rounded-md">
        </div>
        <div class="mb-4">
            <label class="block font-medium mb-1">Note</label>
            <textarea name="note" rows="2" class="w-full px-3 py-2 border rounded-md"></textarea>
        </div>
        <div class="flex justify-end">
            <a href="{{ route('vendor.uconfig.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md mr-2">Annulla</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Salva</button>
        </div>
    </form>
</div>
@endsection
