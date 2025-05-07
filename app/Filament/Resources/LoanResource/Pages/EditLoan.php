<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use App\Models\LoanHistory;
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
        if ($this->record->status === 'devuelto') {
            // 1. Guardar en el historial
            \App\Models\LoanHistory::create([
                'requester'    => $this->record->requester,
                'book_id'      => $this->record->book_id,
                'loan_date'    => $this->record->loan_date,
                'return_date'  => $this->record->return_date,
                'status'       => $this->record->status,
                'user_id'      => $this->record->user_id,
            ]);
    
            // 2. Actualizar el libro como disponible
            $this->record->book->update([
                'status' => 'disponible',
            ]);
    
            // 3. Eliminar el préstamo actual
            $this->record->delete();
    
            // 4. Redirigir al listado de préstamos
            $this->redirect('/admin/loans');
        }
    }
    

    protected function beforeDelete(): void
{
    if ($this->record->status === 'prestado') {
        $this->record->book->update([
            'status' => 'disponible',
        ]);
    }
}

}
