<?php

namespace App\Filament\Resources\BookRemovalResource\Pages;

use App\Filament\Resources\BookRemovalResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanHistory;
use Filament\Notifications\Notification;

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

            // Buscar préstamo activo del libro
            $loan = Loan::where('book_id', $book->id)->first();

            if ($loan) {
                // Guardar el préstamo en el historial antes de eliminarlo
                LoanHistory::create([
                    'loan_date' => $loan->loan_date,
                    'requester' => $loan->requester,
                    'book_id' => $loan->book_id,
                    'return_date' => $loan->return_date,
                    'created_at' => now(),  // Fecha en que se registra la devolución/retirada
                    'status' => 'retirado', // O el estado que consideres apropiado
                    'user_id' => auth()->id(),
                ]);

                // Eliminar préstamo activo
                $loan->delete();

                Notification::make()
                    ->title('Préstamo eliminado y registrado en historial')
                    ->body('El préstamo activo del libro ha sido archivado y eliminado debido al retiro del recurso.')
                    ->success()
                    ->send();
            }
        }
    }
}

