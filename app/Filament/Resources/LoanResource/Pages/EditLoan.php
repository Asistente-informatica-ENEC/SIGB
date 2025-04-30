<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoan extends EditRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function afterSave(): void
    {
        // Si el estado cambió a 'devuelto', actualizamos el estado del libro
        if ($this->record->status === 'devuelto') {
            $this->record->book->update([
                'status' => 'disponible',
            ]);
        }
    }
}
