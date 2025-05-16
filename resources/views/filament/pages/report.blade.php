<x-filament::page>
    <h1 class="text-2xl font-bold mb-4">📊 Generador de Reportes</h1>

    <form wire:submit.prevent="generateReport" class="space-y-4 mb-6">
        <div>
            <label class="block font-medium">Tipo de Reporte</label>
            <select wire:model="reportType" class="w-full rounded border-gray-300">
                <option value="">-- Selecciona un reporte --</option>
                <option value="loans_by_period">Préstamos por período</option>
                <option value="most_borrowed_books">Libros más prestados</option>
                <option value="never_borrowed_books">Libros no prestados</option>
                <option value="active_loans">Préstamos activos</option>
                <option value="loan_history">Historial de préstamos</option>
            </select>
        </div>

        @if(in_array($reportType, ['loans_by_period', 'loan_history']))
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium">Desde</label>
                    <input type="date" wire:model="startDate" class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="block font-medium">Hasta</label>
                    <input type="date" wire:model="endDate" class="w-full rounded border-gray-300" />
                </div>
            </div>
        @endif

        <button type="submit"
                class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 transition">
            Generar Reporte
        </button>
    </form>

    @if($results->isNotEmpty())
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Resultados</h2>
            <table class="w-full text-left border">
                <thead>
                    <tr>
                        @foreach(array_keys($results->first()->getAttributes()) as $key)
                            <th class="border px-2 py-1 capitalize">{{ str_replace('_', ' ', $key) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                        <tr>
                            @foreach($result->getAttributes() as $value)
                                <td class="border px-2 py-1">{{ is_scalar($value) ? $value : json_encode($value) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament::page>
