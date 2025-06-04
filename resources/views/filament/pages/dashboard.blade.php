<x-filament::page>

    <div><livewire:filament.widgets.account-widget /></div>

    <div class="text-2xl font-bold mb-6 text-center">SISTEMA INTEGRAL DE GESTION BIBLIOTECARIA</div>

    <div class="text-2xl font-bold mb-6 text-center">ESCUELA NACIONAL DE ENFERMERÍA DE COBÁN E INDAPSV</div>

    <div class="flex justify-center mb-6 bg-yellow-100">
        <img src="{{ asset('images/logo2.png') }}" alt="Imagen de bienvenida"
            class="w-32 h-32 object-contain" />
    </div>


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

    <div class="text-2xl font-bold mb-6 text-center">Bienvenido al Sistema</div>

    {{-- Aquí va tu widget de préstamos por mes --}}
    <livewire:filament.widgets.prestamos-por-mes />

    {{-- O con helper opcional --}}
    {{-- 
    <x-filament::widgets :widgets="[
        \App\Filament\Widgets\PrestamosPorMes::class,
    ]" />
    --}}

    {{-- Otros contenidos... --}}


</x-filament::page>

