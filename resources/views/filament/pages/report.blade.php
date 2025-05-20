<x-filament::page>
    <div class="space-y-6">

        <!-- Tu formulario para seleccionar reportes -->
        <div class="bg-white p-6 rounded-xl shadow space-y-4">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                📊 Generador de Reportes
            </h1>

            <form wire:submit.prevent="generateReport" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-gray-700">Tipo de Reporte</label>
                        <select wire:model="reportType" class="w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
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
                            <label class="block font-medium text-gray-700">Estado del libro</label>
                            <select wire:model="bookStatus" class="w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
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
                            <label class="block font-medium text-gray-700">Desde</label>
                            <input type="date" wire:model="startDate" class="w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500" />
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700">Hasta</label>
                            <input type="date" wire:model="endDate" class="w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500" />
                        </div>
                    @endif
                </div>

                <div class="text-right">
                    <button class="my-4 mx-2 px-6 py-3 bg-primary-600 text-white text-base font-semibold rounded-lg hover:bg-primary-700 transition duration-200 ease-in-out">
                        Aceptar
                    </button>

                </div>
            </form>
        </div>

 @if($results->isNotEmpty())
            @php
                $columnLabels = [
                    'id' => 'ID',
                    'title' => 'Título',
                    'author_id' => 'Autor',
                    'status' => 'Estado',
                    'type_code_id' => 'Tipo de recurso',
                    'book_code'=>'Código',
                    'loan_date' => 'Fecha de Préstamo',
                    'return_date' => 'Fecha de Devolución',
                    'created_at' => 'Fecha de Registro',
                    'updated_at' => 'Última Actualización',
                    'book_id' => 'Libro',
                    'edition' => 'Edición',
                    'inventory_number' => 'No. Inventario',
                    'requester' => 'Solicitante',
                    'user_id' => 'Registrado por',
                    'physic_location' => 'Ubicación',
                    'themes'=>'Temas',
                    'name' => 'Nombre',
                    'publishing_house_id' => 'Editorial',
                    'publishing_year' => 'Año de publicación',
                    'genre_id' => 'Temática',
                    'total' => 'Total de Préstamos',
                ];

                $first = $results->first();

                if (is_object($first) && method_exists($first, 'getAttributes')) {
                    $keys = array_keys($first->getAttributes());
                } elseif (is_array($first)) {
                    $keys = array_keys($first);
                } else {
                    $keys = [];
                }

                $getLabel = fn($key) => $columnLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
            @endphp

            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📄 Resultados del Reporte</h2>
                <p class="text-gray-600 mb-4">
                    Total de registros: <span class="font-semibold">{{ $results->count() }}</span>
                </p>
                <div class="overflow-auto rounded-lg">
                    <table class="min-w-full border border-gray-300 text-sm text-gray-800">
                        <thead class="bg-gray-100">
                            <tr>
                                @foreach($keys as $key)
                                    <th class="border px-3 py-2 text-left whitespace-nowrap">
                                        {{ $getLabel($key) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $item)
                                <tr class="hover:bg-gray-50">
                                    @foreach($keys as $key)
                                        <td class="border px-3 py-2 whitespace-nowrap">
                                            @php
                                                // Para cada campo, obtener valor
                                                if (is_object($item) && method_exists($item, 'getAttribute')) {
                                                    $value = $item->getAttribute($key);
                                                } elseif (is_array($item) && array_key_exists($key, $item)) {
                                                    $value = $item[$key];
                                                } else {
                                                    $value = null;
                                                }
                                            @endphp
                                            {{ is_null($value) ? '-' : $value }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>

