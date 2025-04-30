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
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    
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
        return $form
            ->schema([
                Hidden::make('user_id')->default(fn()=>Auth::id()),
                TextInput::make('requester')->label('Solicitante')->required(),
                Select::make('book_id')
                ->label('Libro')
                ->default(request()->get('book_id'))
                ->options(function () {
                    return \App\Models\Book::where('status', 'disponible')->pluck('title', 'id');
                })
                ->required()
                ->searchable(),
                DatePicker::make('loan_date')->label('Fecha de prestamo')
                ->default(now())
                ->required(),
                DatePicker::make('return_date')->label('Fecha de devolución')->required(),
                Select::make('status')->label('Estado')
                ->options([
                    'prestado'=>'Prestado',
                    'devuelto'=>'Devuelto',
                ])
                ->default('prestado'),
                
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
                TextColumn::make('book.title')->label('Título')->searchable()->sortable(),
                TextColumn::make('loan_date')->label('Fecha de préstamo')->searchable()->sortable(),
                TextColumn::make('return_date')->label('Fecha establecida para devolución')->searchable()->sortable(),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }
}
