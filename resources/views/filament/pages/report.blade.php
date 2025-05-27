<x-filament::page>
    <div class="space-y-6">

        <!-- Tu formulario para seleccionar reportes -->
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow space-y-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                📊 Generador de Reportes
            </h1>

            <form wire:submit.prevent="generateReport" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-gray-700 dark:text-gray-200">Tipo de Reporte</label>
                        <select wire:model="reportType"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            <option value="">-- Selecciona un reporte --</option>
                            <option value="book_inventory">📚 Inventario de Libros</option>
                            <option value="books_by_status">📘 Estado de los Libros</option>
                            <option value="loans_by_period">📆 Préstamos por período</option>
                            <option value="most_borrowed_books">🔥 Libros más prestados</option>
                            <option value="never_borrowed_books">🕵️ Libros no prestados</option>
                            <option value="active_loans">📕 Préstamos activos</option>
                            <option value="loan_history">📜 Historial de préstamos</option>
                        </select>
                    </div>

                    @if ($reportType === 'books_by_status')
                        <div>
                            <label class="block font-medium text-gray-700 dark:text-gray-200">Estado del libro</label>
                            <select wire:model="bookStatus"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                                <option value="">-- Todos --</option>
                                <option value="disponible">Disponible</option>
                                <option value="prestado">Prestado</option>
                                <option value="retirado">Retirado</option>
                                <option value="reparacion">En Reparación</option>
                            </select>
                        </div>
                    @endif

                    @if(in_array($reportType, ['loans_by_period', 'loan_history']))
                        <div>
                            <label class="block font-medium text-gray-700 dark:text-gray-200">Desde</label>
                            <input type="date" wire:model="startDate"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 dark:text-gray-200">Hasta</label>
                            <input type="date" wire:model="endDate"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                        </div>
                    @endif
                </div>

                <div class="text-right">
                    <button
                        class="my-4 mx-2 px-6 py-3 bg-primary-600 text-white text-base font-semibold rounded-lg hover:bg-primary-700 transition duration-200 ease-in-out">
                        Aceptar
                    </button>
                </div>
            </form>
        </div>

        @if($results->isNotEmpty())
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow">
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">📄 Resultados del Reporte</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Total de registros: <span class="font-semibold">{{ $results->count() }}</span>
                </p>

                <div class="text-right">
                    <button wire:click="exportToPdf"
                        class="my-4 mx-2 px-6 py-3 bg-primary-600 text-white text-base font-semibold rounded-lg hover:bg-primary-700 transition duration-200 ease-in-out">
                        🧾 Generar reporte
                    </button>
                </div>
            </div>
        @endif

    </div>
</x-filament::page>


