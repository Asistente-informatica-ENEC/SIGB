<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Genre;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BooksByGenreOverview extends BaseWidget
{
    protected ?string $heading = 'Conteo de Libros por Temática';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $genres = Genre::all();

        $stats = [];

        foreach ($genres as $genre) {
            $bookCount = Book::where('status', 'disponible')  // <== aquí
                ->whereHas('genres', function ($query) use ($genre) {
                    $query->where('genres.id', $genre->id);
                })
                ->count();

            $stats[] = Stat::make($genre->Género, $bookCount)
                ->description('Libros disponibles')
                ->color('primary');
        }

        return $stats;
    }
}
