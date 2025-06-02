<x-filament::page>
    <div class="text-2xl font-bold mb-6 text-center">📚 Bienvenido al Sistema de Gestión Bibliotecaria</div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total de Libros -->
        <div class="p-6 bg-white rounded-2xl shadow border">
            <h2 class="text-lg font-semibold text-gray-700">Libros en el catálogo</h2>
            <p class="text-4xl text-blue-600 mt-2">{{ \App\Models\Book::count() }}</p>
        </div>

        <!-- Préstamos activos -->
        <div class="p-6 bg-white rounded-2xl shadow border">
            <h2 class="text-lg font-semibold text-gray-700">Préstamos activos</h2>
            <p class="text-4xl text-green-600 mt-2">{{ \App\Models\Loan::whereNull('return_date')->count() }}</p>
        </div>

        <!-- Historial total de préstamos -->
        <div class="p-6 bg-white rounded-2xl shadow border">
            <h2 class="text-lg font-semibold text-gray-700">Historial de préstamos</h2>
            <p class="text-4xl text-purple-600 mt-2">{{ \App\Models\LoanHistory::count() }}</p>
        </div>
    </div>
</x-filament::page>

