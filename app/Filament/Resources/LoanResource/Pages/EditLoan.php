<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use App\Models\LoanHistory;
use App\Models\Book;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Actions as ComponentsActions;
use Filament\Notifications\Notification;
use Filament\Actions\CancelAction; 

class EditLoan extends EditRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botón para generar vale de préstamo
            Actions\Action::make('generar_vale')
                ->label('Generar Vale de Préstamo')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function ($record) {
                    $pdf = Pdf::loadView('pdf.vale-prestamo', [
                        'loans' => collect([$record]),
                    ]);

                    return response()->streamDownload(
                        fn () => print($pdf->stream()),
                        "vale-prestamo-{$record->id}.pdf"
                    );
                })
                ->visible(fn ($record) => $record->status === 'prestado'),

            // Botón para marcar como devuelto
            Actions\Action::make('marcarComoDevuelto')
                ->label('Marcar como devuelto')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'prestado')
                ->action(function ($record) {
                    // 1. Guardar en historial
                    LoanHistory::create([
                        'loan_id'     => $record->id,
                        'requester'   => $record->requester,
                        'book_id'     => $record->book_id,
                        'loan_date'   => $record->loan_date,
                        'return_date' => $record->return_date,
                        'procedencia'=> $record->procedencia,
                        'user_id'     => $record->user_id,
                        'status'      => 'devuelto',
                    ]);

                    

                    // 2. Actualizar libro como disponible
                    $record->book->update(['status' => 'disponible']);

                    // 3. Eliminar préstamo activo
                    $record->delete();

                    // 4. Notificar y redirigir
                    Notification::make()
                        ->title('Préstamo marcado como devuelto.')
                        ->success()
                        ->send();

                    $this->redirect(LoanResource::getUrl());
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [
        Action::make('cancelar')
            ->label('Regresar')
            ->url(LoanResource::getUrl())
            ->color('gray')
        ];
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

