<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeCodeResource\Pages;
use App\Filament\Resources\TypeCodeResource\RelationManagers;
use App\Models\TypeCode;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeCodeResource extends Resource
{
    protected static ?string $model = TypeCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static ?string $navigationGroup = 'Gestión de Biblioteca';

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
            'index' => Pages\ListTypeCodes::route('/'),
            'create' => Pages\CreateTypeCode::route('/create'),
            'edit' => Pages\EditTypeCode::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Código';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Códigos';
    }
}
