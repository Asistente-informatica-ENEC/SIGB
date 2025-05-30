<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanHistoryResource\Pages;
use App\Filament\Resources\LoanHistoryResource\RelationManagers;
use App\Models\LoanHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanHistoryResource extends Resource
{
    protected static ?string $model = LoanHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static ?string $navigationGroup = 'Préstamos';

    public static function getModelLabel(): string
    {
        return 'Historial de préstamos';
    }
    
    public static function getPluralModelLabel(): string
    {
        return 'Historial de préstamos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('loan_date')->label('Fecha de Préstamo')->date('d/m/Y H:i')->sortable(),
                TextColumn::make('requester')->label('Solicitante')->searchable()->sortable(),
                TextColumn::make('book.title')->label('Título del Libro')->searchable()->sortable(),
                TextColumn::make('return_date')->label('Fecha para Devolución')->date('d/m/Y')->sortable(),
                TextColumn::make('created_at')->label('Fecha de Devolución')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('status')->label('Estado')->badge()->sortable(),
                TextColumn::make('user.name')->label('Gestionado por')->searchable()->sortable(),
            ])
            ->defaultSort('loan_date', 'desc')

            ->filters([
                //
            ])
            ->actions([

            ])
            ->bulkActions([
                    Tables\Actions\BulkAction::make('exportar_pdf')
                        ->label('Exportar como PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.historial', [
                                'loanHistories' => $records,
                            ]);

                            return response()->streamDownload(
                                fn () => print($pdf->stream()),
                                'historial.pdf'
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                    ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoanHistories::route('/'),

        ];
    }
}
