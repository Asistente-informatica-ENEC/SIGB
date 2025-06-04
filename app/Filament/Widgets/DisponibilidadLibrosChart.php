<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Book;

class DisponibilidadLibrosChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = '1/3';
    protected static ?string $heading = 'Disponibilidad de Libros';

    protected function getData(): array
    {
        $disponibles = Book::where('status', 'disponible')->count();
        $prestados   = Book::where('status', 'prestado')->count();
        $revision    = Book::where('status', 'reparacion')->count();
        $retirados   = Book::where('status', 'retirado')->count(); // nuevo conteo

        return [
            'datasets' => [
                [
                    'label' => 'Disponibilidad',
                    'data' => [$disponibles, $prestados, $revision, $retirados],
                    'backgroundColor' => [
                        '#10b981', // verde - disponibles
                        '#ef4444', // amarillo - prestados
                        '#f59e0b', // rojo - en revisión
                        '#6b7280', // gris - retirados (Tailwind slate-500)
                    ],
                ],
            ],
            'labels' => ['Disponibles', 'Prestados', 'En reparación', 'Retirados'],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // También puedes usar 'doughnut'
    }
}
