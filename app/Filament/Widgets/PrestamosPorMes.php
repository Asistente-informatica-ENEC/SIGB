<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\LoanHistory;
use Illuminate\Support\Facades\DB;

class PrestamosPorMes extends ChartWidget
{
    protected static ?string $heading = '📅 Préstamos registrados';
    protected static ?int $sort = 1;

    protected function getType(): string
    {
        return 'bar'; // o 'bar', 'doughnut', etc.
    }

    protected function getData(): array
    {
        $data = LoanHistory::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $data->pluck('month')->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Préstamos',
                    'data' => $values,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
}

