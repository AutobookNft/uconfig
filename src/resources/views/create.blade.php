<x-app-layout>
    @include('vendor.uconfig._internal_navbar')
    <div class="container mx-auto py-10">
        <h2 class="text-3xl font-bold mb-6 text-gray-800">Crea Nuova Configurazione</h2>
        <div class="bg-white p-8 rounded-lg shadow-md">
            <form method="POST" action="{{ route('uconfig.store') }}">
                @csrf

                <div class="mb-6">
                    <label for="key" class="block text-lg font-semibold text-gray-700 mb-2">Chiave</label>
                    <input type="text" id="key" name="key" class="form-input mt-1 block w-full rounded-md border border-gray-300" required>
                </div>

                <div class="mb-6">
                    <label for="value" class="block text-lg font-semibold text-gray-700 mb-2">Valore</label>
                    <input type="text" id="value" name="value" class="form-input mt-1 block w-full rounded-md border border-gray-300" required>
                </div>

                <div class="mb-6">
                    <label for="category" class="block text-lg font-semibold text-gray-700 mb-2">Categoria</label>
                    <input type="text" id="category" name="category" class="form-input mt-1 block w-full rounded-md border border-gray-300">
                </div>

                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg shadow-md">Crea</button>
            </form>
        </div>
    </div>
    @include('vendor.uconfig.footer')
</x-app-layout>
