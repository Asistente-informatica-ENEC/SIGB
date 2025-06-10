<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublishingHouseResource\Pages;
use App\Models\Publishing_House;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Illuminate\Database\QueryException;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

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
                DeleteAction::make()
                    ->using(function ($record) {
                        try {
                            $record->delete();

                            Notification::make()
                                ->title('Editorial eliminada')
                                ->body('La editorial se eliminó correctamente.')
                                ->success()
                                ->send();

                            return $record;
                        } catch (QueryException $e) {
                            if ($e->getCode() === '23000') {
                                Notification::make()
                                    ->title('No se puede eliminar la editorial')
                                    ->body('La editorial está asociada a uno o más libros y no puede ser eliminada.')
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                return false;
                            }

                            throw $e;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $deleted = 0;
                            $errors = 0;

                            foreach ($records as $record) {
                                try {
                                    $record->delete();
                                    $deleted++;
                                } catch (QueryException $e) {
                                    if ($e->getCode() === '23000') {
                                        $errors++;
                                    } else {
                                        throw $e;
                                    }
                                }
                            }

                            if ($errors > 0) {
                                Notification::make()
                                    ->title('No se pudieron eliminar algunas editoriales')
                                    ->body("Se eliminaron {$deleted} editoriales. {$errors} no se pudieron eliminar por estar asociadas a libros.")
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            } elseif ($deleted > 0) {
                                Notification::make()
                                    ->title('Editoriales eliminadas')
                                    ->body("Se eliminaron correctamente {$deleted} editoriales.")
                                    ->success()
                                    ->send();
                            }
                        }),
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

