<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View; // Importa View

class MyAccountWidget extends Widget
{
    protected static string $view = 'filament.widgets.my-account-widget';

    protected static ?int $sort = 1;

    // Puedes ajustar el ancho aquí para que ocupe lo que necesitas
    protected int | string | array $columnSpan = 'full'; 
        

    // Opcional: Si necesitas datos dinámicos en tu vista
    // protected function getViewData(): array
    // {
    //     return [
    //         'user' => auth()->user(),
    //     ];
    // }
}