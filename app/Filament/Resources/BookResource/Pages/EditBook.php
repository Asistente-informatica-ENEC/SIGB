<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str; // Importa la clase Str

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('duplicate') // Aquí agregamos la acción de duplicar
                ->label('Duplicar')
                ->icon('heroicon-o-document-duplicate')
                ->tooltip('Duplicar este recurso bibliográfico')
                ->color('info')
                ->action(function () { // Se ejecuta cuando se hace clic en el botón
                    $record = $this->getRecord(); // Obtiene el registro actual que se está editando

                    $duplicatedBook = $record->replicate();
                    // Modificamos campos para la copia
                    $duplicatedBook->title = $record->title;
                    $duplicatedBook->book_code = $record->book_code;
                    $duplicatedBook->inventory_number = '#';
                    $duplicatedBook->status = 'disponible';

                    $duplicatedBook->save();

                    // Sincroniza las relaciones muchos a muchos
                    $duplicatedBook->authors()->sync($record->authors->pluck('id'));
                    $duplicatedBook->genres()->sync($record->genres->pluck('id'));

                    // Notifica al usuario
                    \Filament\Notifications\Notification::make()
                        ->title('Libro duplicado correctamente')
                        ->success()
                        ->send();

                    // Redirige al formulario de edición del libro duplicado
                    return redirect()->route('filament.admin.resources.books.edit', ['record' => $duplicatedBook->id]);
                }),
            Actions\DeleteAction::make(), // El botón de eliminar existente
        ];
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('cancel')
            ->label('Regresar')
            ->url($this->getResource()::getUrl());
    }
}
