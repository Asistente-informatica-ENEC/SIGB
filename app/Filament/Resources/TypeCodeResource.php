<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeCodeResource\Pages;
use App\Models\TypeCode;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Filament\Tables\Actions\DeleteAction;

class TypeCodeResource extends Resource
{
    protected static ?string $model = TypeCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static ?string $navigationGroup = 'Gestión de Biblioteca';

    public static function getModelLabel(): string
    {
        return 'Código';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Códigos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('código')->required()->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('código')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->before(function ($record, DeleteAction $action) {
                        try {
                            $record->delete();
                            $action->halt(); 
                        } catch (QueryException $e) {
                            if ($e->getCode() === '23000') {
                                Notification::make()
                                    ->title('No se puede eliminar')
                                    ->body('Este código está asociado a uno o más libros y no puede ser eliminado.')
                                    ->danger()
                                    ->persistent()
                                    ->send();
                                $action->halt();
                            } else {
                                throw $e;
                            }
                        }
                    })
                    ->action(fn () => null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records, $action) {
                            foreach ($records as $record) {
                                try {
                                    $record->delete();
                                } catch (\Illuminate\Database\QueryException $e) {
                                    if ($e->getCode() === '23000') {
                                        Notification::make()
                                            ->title('No se pueden eliminar algunos códigos')
                                            ->body('Uno o más códigos están asociados a libros y no se pueden eliminar.')
                                            ->danger()
                                            ->persistent()
                                            ->send();

                                        $action->halt();
                                        break;
                                    } else {
                                        throw $e;
                                    }
                                }
                            }

                            $action->halt(); // Evita que se intente eliminar de nuevo
                        })
                        ->action(fn () => null),
                ]),
            ]);

    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypeCodes::route('/'),
            'create' => Pages\CreateTypeCode::route('/create'),
            'edit' => Pages\EditTypeCode::route('/{record}/edit'),
        ];
    }
}

