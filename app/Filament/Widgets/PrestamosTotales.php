<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\LoanHistory;

class PrestamosTotales extends Widget
{
    protected static ?string $heading = 'Total de préstamos registrados';

    protected function getViewData(): array
    {
        return [
            'totalPrestamos' => LoanHistory::count(),
        ];
    }

    protected static string $view = 'filament.widgets.prestamos-totales';
}
