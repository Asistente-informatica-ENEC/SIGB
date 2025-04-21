<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use App\Models\Genre;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    public static function getModelLabel(): string
    {
        return 'Libro';
    }
    
    public static function getPluralModelLabel(): string
    {
        return 'Libros';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->label('Título')->required()->maxLength(255),
                Select::make('type_code_id')->label('Tipo de recurso')->relationship('typeCode','name')->required(),
                TextInput::make('book_code')->label('código de recurso')->required()->maxLength(255),
                Select::make('status')->label('Estado')->options([
                    'disponible'=>'Disponible',
                    'prestado'=>'Prestado',
                    'reparacion'=>'En reparación',
                    'retirado'=>'Retirado',
                ])->required()->default('disponible')->native(false),
                Select::make('author_id')->label('Autor/es')->relationship('authors','name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' ' . $record->lastname_1)
                ->multiple()
                ->required(),
                Select::make('publishing_house_id')->label('Editorial')->relationship('publishingHouse', 'name')->required(),
                TextInput::make('publishing_year')->label('Año de publicación')->required()->maxLength(255),
                TextInput::make('edition')->label('Edición')->required()->maxLength(255),
                Select::make('genres')
                    ->label('Género/s')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->relationship('genres', 'Género') // <- esto ya hace todo por ti
                    ->required(),
                TextInput::make('inventory_number')->label('Número de inventario')->required()->maxLength(255),
                TextInput::make('physic_location')->label('Ubicación')->required()->maxLength(255),
                Textarea::make('themes')
                ->label('Temas')
                ->maxLength(500)
                ->rows(10) // opcional: define cuántas líneas se muestran
                ->columnSpan('full'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->searchable()->sortable(),
                TextColumn::make('typeCode.name')->label('Tipo de Código')->searchable()->sortable(),
                TextColumn::make('book_code')->label('código de recurso')->searchable()->sortable(),
                BadgeColumn::make('status')->label('Estado')->formatStateUsing(fn (string $state): string => match ($state) {
                    'disponible' => 'Disponible',
                    'prestado' => 'Prestado',
                    'reparacion' => 'En reparación',
                    'no_disponible' => 'No disponible',
                    default => ucfirst($state),
                })
                    ->colors([
                        'success' => 'disponible',
                        'danger' => 'prestado',
                        'info' => 'reparacion',
                        'gray' => 'retirado',
                    ]),
                    TextColumn::make('authors')
                    ->label('Autor/es')
                    ->getStateUsing(function ($record) {
                        return $record->authors
                            ->map(fn ($author) => $author->name . ' ' . $author->lastname_1)
                            ->join(', ');
                    })
                    ->sortable()
                        ->searchable(query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('authors', function (Builder $query) use ($search) {
                                $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('lastname_1', 'like', "%{$search}%");
                            });
                        }),
                TextColumn::make('publishingHouse.name')->label('Editorial')->searchable()->sortable(),
                TextColumn::make('publishing_year')->label('Año de publicación')->searchable()->sortable(),
                TextColumn::make('edition')->label('Edición')->searchable()->sortable(),
                TextColumn::make('genres.Género')
                ->label('Género/s')
                ->badge()
                ->separator(', '),
                TextColumn::make('inventory_number')->label('Número de inventario')->searchable()->sortable(),
                TextColumn::make('physic_location')->label('Ubicación')->searchable()->sortable(),
                TextColumn::make('themes')->label('Temas')->limit(25)
                ->tooltip(fn ($record) => $record->themes)->searchable()->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Agrega la acción de "Ver"
                Tables\Actions\EditAction::make(), // Mantén la acción de "Editar" en la columna de acciones
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('title') // Define una columna de ordenamiento por defecto
            ->recordUrl(null); // Deshabilita la redirección al hacer clic en la fila
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
