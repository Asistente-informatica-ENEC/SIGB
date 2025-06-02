<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Loan;

class PrestamosPorMes extends ChartWidget
{
    protected static ?string $heading = 'Préstamos por Mes';
    protected static ?int $sort = 1; // Orden en que aparece en el dashboard

    protected function getData(): array
    {
        $data = Loan::selectRaw("MONTH(created_at) as mes, COUNT(*) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $labels = $data->map(fn ($item) => \Carbon\Carbon::create()->month($item->mes)->locale('es')->monthName)->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Préstamos',
                    'data' => $values,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // También puedes usar 'line', 'pie', etc.
    }
}

