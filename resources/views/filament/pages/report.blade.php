<x-filament::page>
    <h1 class="text-2xl font-bold mb-4">Reportes</h1>

    <div class="grid grid-cols-3 gap-4">
        <div class="p-4 bg-white rounded shadow">
            <p class="text-lg">Total de Libros</p>
            <p class="text-2xl font-semibold">{{ \App\Models\Book::count() }}</p>
        </div>
        <div class="p-4 bg-white rounded shadow">
            <p class="text-lg">Libros Prestados</p>
            <p class="text-2xl font-semibold">{{ \App\Models\Book::where('status', 'prestado')->count() }}</p>
        </div>
        <div class="p-4 bg-white rounded shadow">
            <p class="text-lg">Préstamos Totales</p>
            <p class="text-2xl font-semibold">{{ \App\Models\Loan::count() }}</p>
        </div>
    </div>
</x-filament::page>
