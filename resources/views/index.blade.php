@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Gestione Configurazioni</h1>
    <a href="{{ route('uconfig.create') }}" class="btn btn-primary">Aggiungi Configurazione</a>
    <table class="table">
        <thead>
            <tr>
                <th>Chiave</th>
                <th>Categoria</th>
                <th>Valore</th>
                <th>Note</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($configs as $config)
            <tr>
                <td>{{ $config->key }}</td>
                <td>{{ $config->category }}</td>
                <td>{{ $config->value }}</td>
                <td>{{ $config->note }}</td>
                <td>
                    <a href="{{ route('uconfig.edit', $config->key) }}" class="btn btn-warning">Modifica</a>
                    <form action="{{ route('uconfig.destroy', $config->key) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Elimina</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
