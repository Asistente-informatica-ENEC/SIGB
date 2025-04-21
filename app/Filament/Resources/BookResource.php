<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->label('Título')->required()->maxLength(255),
                Select::make('type_code_id')->label('Tipo de recurso')->relationship('typeCode','name')->required(),
                TextInput::make('book_code')->label('código de recurso')->required()->maxLength(255),
                Select::make('author_id')->label('Autor/es')->relationship('authors','name')->required(),
                Select::make('publishing_house_id')->label('Editorial')->relationship('publishingHouse', 'name')->required(),
                TextInput::make('publishing_year')->label('Año de publicación')->required()->maxLength(255),
                TextInput::make('edition')->label('Edición')->required()->maxLength(255),
                Select::make('genre_id')->label('Género/s')->relationship('genres','Género')->required(),
                TextInput::make('inventory_number')->label('Número de inventario')->required()->maxLength(255),
                TextInput::make('physic_location')->label('Ubicación')->required()->maxLength(255),
                TextInput::make('themes')->label('Temas')->maxLength(1000),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->searchable()->sortable(),
                TextColumn::make('typeCode.name')->label('Tipo de Código')->searchable()->sortable(),
                TextColumn::make('book_code')->label('código de recurso')->searchable()->sortable(),
                TextColumn::make('authors.name')->label('Autores')->limit(10)->searchable()->sortable(),
                TextColumn::make('publishingHouse.name')->label('Editorial')->searchable()->sortable(),
                TextColumn::make('publishing_year')->label('Año de publicación')->searchable()->sortable(),
                TextColumn::make('edition')->label('Edición')->searchable()->sortable(),
                TextColumn::make('genres.name')->label('Géneros')->limit(3)->searchable()->sortable(),
                TextColumn::make('inventory_number')->label('Número de inventario')->searchable()->sortable(),
                TextColumn::make('physic_location')->label('Ubicación')->searchable()->sortable(),
                TextColumn::make('themes')->label('Temas')->searchable()->sortable(),

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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
