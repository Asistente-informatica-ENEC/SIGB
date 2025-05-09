<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Filament\Resources\LoanResource\RelationManagers;
use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\BadgeColumn;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportDisablingBackButtonCache\DisableBackButtonCacheMiddleware;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static ?string $navigationGroup = 'Préstamos';

    
    public static function getModelLabel(): string
    {
        return 'Préstamo';
    }
    
    public static function getPluralModelLabel(): string
    {
        return 'Préstamos';
    }

    public static function form(Form $form): Form
    {
        $isEdit = request()->routeIs('filament.admin.resources.loans.edit');
        
        return $form
            ->schema([
                Hidden::make('user_id')->default(fn()=>Auth::id()),
                TextInput::make('requester')->label('Solicitante')
                ->disabled($isEdit)
                ->required(),
                Select::make('book_id')
                ->label('Libro')
                ->relationship('book', 'title')
                ->searchable()
                ->preload()
                ->options(fn () => \App\Models\Book::where('status', 'disponible')->pluck('title', 'id'))
                ->default(request()->get('book_id'))
                ->disabled(fn () => request()->has('book_id'))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->title)
                ->disabled($isEdit)
                ->required(),
                DatePicker::make('loan_date')->label('Fecha de prestamo')
                ->default(now())
                ->disabled(fn (string $context) => $context === 'create')
                ->disabled($isEdit)
                ->required(),
                DatePicker::make('return_date')->label('Fecha de devolución')->required()
                ->disabled($isEdit),
                Radio::make('procedencia')
                ->label('Procedencia académica')
                ->options([
                    'Técnico en Enfermería - 1er año' => 'Técnico en Enfermería - 1er año',
                    'Técnico en Enfermería - 2do año' => 'Técnico en Enfermería - 2do año',
                    'Técnico en Enfermería - 3er año' => 'Técnico en Enfermería - 3er año',
                    'Licenciatura en Enfermería - 4to año' => 'Licenciatura en Enfermería - 4to año',
                    'Licenciatura en Enfermería - 5to año' => 'Licenciatura en Enfermería - 5to año',
                    'Auxiliares de Enfermería - A' => 'Auxiliares de Enfermería - A',
                    'Auxiliares de Enfermería - B' => 'Auxiliares de Enfermería - B',
                    'Auxiliares de Enfermería - C' => 'Auxiliares de Enfermería - C',
                    'Auxiliares de Enfermería - D' => 'Auxiliares de Enfermería - D',
                    'Laboratorio Clínico' => 'Laboratorio Clínico',
                    'Personal' => 'Personal',
                    'Externo' => 'Externo',
                ])
                ->columns(3)
                ->columnSpanFull()
                ->required()
                ->Disabled($isEdit),
                
            ]);

    }

    public static function table(Table $table): Table
    {
        $hayRetrasos = Loan::query()
        ->where('status', 'prestado')
        ->whereDate('return_date', '<', now())
        ->exists();

        return $table
            ->columns([
                TextColumn::make('requester')->label('Solicitante')->searchable()->sortable(),
                TextColumn::make('procedencia')->label('Procedencia')->sortable()->searchable()->limit(15)
                ->tooltip(fn ($record) => $record->procedencia),
                TextColumn::make('book.title')->label('Título')
                ->limit(50)
                ->tooltip(fn ($record) => $record->title)
                ->searchable()->sortable(),
                TextColumn::make('loan_date')->label('Fecha de préstamo')->dateTime('d/m/Y H:i')->searchable()->sortable(),
                TextColumn::make('return_date')->label('Fecha para devolución')->dateTime('d/m/Y H:i')->searchable()->sortable(),
                TextColumn::make('status')->label('Estado')->badge()->searchable()->sortable(),
            ...($hayRetrasos ? [
                TextColumn::make('estado_entrega')
                    ->label('Estado de Entrega')
                    ->getStateUsing(function ($record) {
                        if (
                            isset($record->status, $record->return_date) &&
                            $record->status === 'prestado' &&
                            $record->return_date !== null &&
                            now()->gt($record->return_date)
                        ) {
                            return 'Retrasado';
                        }

                        return null;
                    })
                    ->color('danger')
                    ->searchable()
                    ->sortable(),
                    ] : []),
                    ...($hayRetrasos ? [
                        TextColumn::make('estado_entrega')
                            ->label('Estado de Entrega')
                            ->getStateUsing(function ($record) {
                                if (
                                    isset($record->status, $record->return_date) &&
                                    $record->status === 'prestado' &&
                                    $record->return_date !== null &&
                                    now()->gt($record->return_date)
                                ) {
                                    return 'Retrasado';
                                }
                    
                                return null;
                            })
                            ->color('danger')
                            ->sortable(),
                    
                        TextColumn::make('dias_retraso')
                            ->label('Días de Retraso')
                            ->getStateUsing(function ($record) {
                                if (
                                    isset($record->status, $record->return_date) &&
                                    $record->status === 'prestado' &&
                                    $record->return_date !== null &&
                                    now()->gt($record->return_date)
                                ) {
                                    return now()->diffInDays($record->return_date);
                                }
                    
                                return null;
                            })
                            ->color('danger')
                            ->sortable(),
                    ] : []),
                    
                TextColumn::make('user.name')->label('Gestionado por')->searchable()->sortable(),
            ])
            ->defaultSort('loan_date', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('exportar_pdf')
                    ->label('Generar vale de préstamo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (\Illuminate\Support\Collection $records) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.vale-prestamo', [
                            'loans' => $records,
                        ]);
                
                        return response()->streamDownload(
                            fn () => print($pdf->stream()),
                            'vales-prestamo.pdf'
                        );
                    })
                
        ->deselectRecordsAfterCompletion(),
    
                    ]),
                ])
                
                ->recordUrl(null); 
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
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),

        ];
    }


}
