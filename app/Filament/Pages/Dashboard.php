<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\MyAccountWidget;
use App\Filament\Widgets\BooksByGenreOverview;
use App\Filament\Widgets\PrestamosPorMes;
use Filament\Widgets\FilamentInfoWidget; // Si quieres incluir este widget por defecto

class Dashboard extends BaseDashboard
{
    // Define el número de columnas para tu dashboard.
    // Un valor de 3 es bueno para el widget de cuenta y dos más pequeños al inicio.
    public function getColumns(): int | string | array
    {
        return 3;
        // O: return ['default' => 1, 'sm' => 2, 'xl' => 3]; // para responsividad
    }

    public function getWidgets(): array
    {
        return [
            // Fila 1: 3 columnas
            MyAccountWidget::class, // Asumiendo columnSpan = '1/3'
            // Puedes añadir otro widget pequeño aquí si quieres rellenar la 3ra columna

            // Fila 2: Conteo de libros (full)
            BooksByGenreOverview::class, // Asumiendo columnSpan = 'full'

            // Fila 3: Gráfica de préstamos (full)
            PrestamosPorMes::class, // Asumiendo columnSpan = 'full'
        ];
    }
}
