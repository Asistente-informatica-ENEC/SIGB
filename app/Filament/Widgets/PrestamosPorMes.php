<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\LoanHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PrestamosPorMes extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = '1/2';
    protected static ?string $heading = '📅 Préstamos realizados';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'last_30_days' => 'Últimos 30 días',
            'last_3_months' => 'Últimos 3 meses',
            'last_4_months' => 'Últimos 4 meses',
            'last_6_months' => 'Últimos 6 meses',
            'this_year' => 'Este año',
            'all' => 'Todo',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? 'last_30_days'; // valor por defecto

        $query = LoanHistory::query();

        switch ($filter) {
            case 'last_30_days':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case 'last_3_months':
                $query->where('created_at', '>=', now()->subMonths(3));
                break;
            case 'last_4_months':
                $query->where('created_at', '>=', now()->subMonths(4));
                break;
            case 'last_6_months':
                $query->where('created_at', '>=', now()->subMonths(6));
                break;
            case 'this_year':
                $query->whereYear('created_at', now()->year);
                break;
            case 'all':
                // sin filtros
                break;
        }

        $data = $query
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $labels = $data->pluck('period')->toArray();
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

