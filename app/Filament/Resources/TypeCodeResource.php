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
use Filament\Tables\Actions\DeleteBulkAction;
// use Illuminate\Validation\Rule; // Esta línea no es necesaria para esta corrección, pero la puedes mantener

class TypeCodeResource extends Resource
{
    protected static ?string $model = TypeCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static ?string $navigationGroup = 'Gestión de Biblioteca';

    public static function getModelLabel(): string
    {
        return 'Tipos de recurso';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tipos de recurso';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Tipo de recurso')
                    ->required()
                    ->maxLength(255)
                    // CAMBIO AQUÍ: Usar argumentos posicionales en lugar de nombrados
                    // El primer argumento es la tabla (opcional si es el modelo del campo),
                    // el segundo es la columna (opcional si es el nombre del campo),
                    // el tercero es el record a ignorar, y el cuarto el mensaje.
                    ->unique(
                        table: TypeCode::class, // Opcional, pero explícito es bueno. Si no lo pones, Filament lo inferirá.
                        column: 'name',         // Opcional, Filament lo inferirá del nombre del campo 'name'.
                        ignoreRecord: true,      // Aquí se pasa el valor booleano directamente
                        // Nota: El mensaje personalizado se define con ->validationMessages() o ->validationAttribute()
                        // o de forma más global en Filament.
                    )
                    ->validationMessages([
                        'unique' => 'El nombre del tipo de recurso ya existe.',
                    ])
                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tipo de recurso')->searchable()->sortable(),
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
                                ->title('Tipo de recurso eliminado')
                                ->body('El tipo de recurso ha sido eliminado exitosamente.')
                                ->success()
                                ->send();

                            return $record;
                        } catch (QueryException $e) {
                            if ($e->getCode() === '23000') {
                                Notification::make()
                                    ->title('No se puede eliminar')
                                    ->body('Este código está asociado a uno o más libros y no puede ser eliminado.')
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                return false; // Evita que Filament cierre el modal como si fuera exitoso
                            }

                            throw $e;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $deletedCount = 0;
                            $errorCount = 0;

                            foreach ($records as $record) {
                                try {
                                    $record->delete();
                                    $deletedCount++;
                                } catch (QueryException $e) {
                                    if ($e->getCode() === '23000') {
                                        $errorCount++;
                                    } else {
                                        throw $e;
                                    }
                                }
                            }

                            if ($errorCount > 0) {
                                Notification::make()
                                    ->title('No se pudieron eliminar todos los códigos')
                                    ->body("Se eliminaron {$deletedCount} códigos. {$errorCount} códigos están asociados a libros y no se pudieron eliminar.")
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            } elseif ($deletedCount > 0) {
                                Notification::make()
                                    ->title('Códigos eliminados')
                                    ->body("{$deletedCount} códigos han sido eliminados exitosamente.")
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
            'index' => Pages\ListTypeCodes::route('/'),
            'create' => Pages\CreateTypeCode::route('/create'),
            'edit' => Pages\EditTypeCode::route('/{record}/edit'),
        ];
    }
}