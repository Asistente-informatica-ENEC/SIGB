<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublishingHouseResource\Pages;
use App\Filament\Resources\PublishingHouseResource\RelationManagers;
use App\Models\Publishing_House;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PublishingHouseResource extends Resource
{
    protected static ?string $model = Publishing_House::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static ?string $navigationGroup = 'Gestión de Biblioteca';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                TextInput::make('location')->label('Localización')->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('location')->label('Localización')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record, $action) {
                        try {
                            $record->delete();
                            $action->halt(); // Para evitar ejecución doble
                        } catch (\Illuminate\Database\QueryException $e) {
                            if ($e->getCode() === '23000') {
                                \Filament\Notifications\Notification::make()
                                    ->title('No se puede eliminar la editorial')
                                    ->body('La editorial está asociada a uno o más libros y no puede ser eliminada.')
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                $action->halt(); // Detiene la acción predeterminada
                            } else {
                                throw $e; // Re-lanza errores inesperados
                            }
                        }
                    })
                    ->action(fn () => null), // Cancela la acción por defecto
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
                                            ->title('No se pueden eliminar algunas editoriales')
                                            ->body('Una o más editoriales están asociadas a libros y no se pueden eliminar.')
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

                            $action->halt(); // Detiene la acción predeterminada de eliminación masiva
                        })
                        ->action(fn () => null),
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
            'index' => Pages\ListPublishingHouses::route('/'),
            'create' => Pages\CreatePublishingHouse::route('/create'),
            'edit' => Pages\EditPublishingHouse::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Editorial';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Editoriales';
    }
}
