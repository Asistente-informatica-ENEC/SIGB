<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;

class LibrosMasPrestados extends ChartWidget
{
    protected static ?string $heading = 'Libros más prestados';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Loan::select('book_title', DB::raw('count(*) as total'))
            ->groupBy('book_title')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Préstamos',
                    'data' => $data->pluck('total'),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                    ],
                ],
            ],
            'labels' => $data->pluck('book_title')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

