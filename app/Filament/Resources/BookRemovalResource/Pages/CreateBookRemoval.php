<?php

namespace App\Filament\Resources\BookRemovalResource\Pages;

use App\Filament\Resources\BookRemovalResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Book;

class CreateBookRemoval extends CreateRecord
{
    protected static string $resource = BookRemovalResource::class;

    protected function afterCreate(): void
    {
        // Actualizar estado del libro a "retirado"
        $book = Book::find($this->record->book_id);
        if ($book) {
            $book->status = 'retirado';
            $book->save();
        }
    }
}

