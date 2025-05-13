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
use Illuminate\Support\Collection;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static ?string $navigationGroup = 'Gestión de Biblioteca';
    
    public static function getModelLabel(): string
    {
        return 'Catálogo';
    }
    
    public static function getPluralModelLabel(): string
    {
        return 'Catálogo';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->label('Título')->required()->maxLength(255),
                Select::make('type_code_id')->label('Tipo de recurso')->relationship('typeCode','name')
                        ->createOptionForm([
                                TextInput::make('name')->required()->label('Tipo de recurso'),
                                ])                
                                ->required(),
                TextInput::make('book_code')->label('código de recurso')->required()->maxLength(255),
                Select::make('status')->label('Estado')->options([
                    'disponible' => 'Disponible',
                    'prestado' => 'Prestado',
                    'reparacion'=>'En reparación',
                    'retirado'=>'Retirado',
                ])->required()->default('disponible')->native(false)
                ->disabled(function ($record) {
                    // Si no hay registro (creación), no deshabilitar
                    if (!$record) {
                        return false;
                    }
                    // Deshabilitar si el libro tiene préstamos activos
                    return $record->status === 'prestado' && $record->loans()->where('return_date', null)->exists();
                }),
                    Select::make('author_id')
                        ->label('Autor/es')
                        ->relationship(
                            name: 'authors',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->select('authors.id', 'authors.name', 'authors.lastname_1')
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' ' . $record->lastname_1)
                        ->searchable()
                        ->preload()
                        ->multiple()
                        ->required()
                        ->createOptionForm([
                                TextInput::make('name')->required()->label('Nombre'),
                                TextInput::make('lastname_1')->label('Apellido')->required(),
                                ])
                        ->getSearchResultsUsing(function (string $search) {
                            return \App\Models\Author::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('lastname_1', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($author) => [
                                    $author->id => $author->name . ' ' . $author->lastname_1
                                ]);
                        }),

                Select::make('publishing_house_id')->label('Editorial')->relationship('publishingHouse', 'name')
                            ->createOptionForm([
                                    TextInput::make('name')->required()->label('Nombre'),
                                    TextInput::make('location')->label('Localización')->required(),
                                ]) 
                ->searchable()                               
                ->required(),
                TextInput::make('publishing_year')->label('Año de publicación')->required()->maxLength(255),
                TextInput::make('edition')->label('Edición')->required()->maxLength(255),
                Select::make('genres')
                    ->label('Género/s')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->relationship('genres', 'Género')
                    ->createOptionForm([
                            TextInput::make('Género')->required()->label('Género'),
                            ]) 
                    ->required(),
                TextInput::make('inventory_number')->label('Número de inventario')->required()->maxLength(255),
                TextInput::make('physic_location')->label('Ubicación')->required()->maxLength(255),
                Textarea::make('themes')
                ->label('Temas')
                ->maxLength(1000)
                ->rows(10)
                ->columnSpan('full')
                ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->limit(50)
                ->tooltip(fn ($record) => $record->title)
                ->searchable()->sortable(),
                TextColumn::make('codigo') 
                ->label('Código')
                ->getStateUsing(function ($record) {
                    return $record->typeCode->name . '-' . $record->book_code;
                })
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query->whereHas('typeCode', function (Builder $query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })->orWhere('book_code', 'like', "%{$search}%");
                })
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->orderBy('type_code_id', $direction)
                        ->orderBy('book_code', $direction);
                }),
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
                        'warning' => 'reparacion',
                        'gray' => 'retirado',
                    ]),
                    TextColumn::make('authors')
                    ->label('Autor/es')->limit(25)
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
                TextColumn::make('physic_location')->label('Ubicación')->limit(50)->searchable()->sortable(),
                TextColumn::make('themes')->label('Temas')->limit(25)
                ->tooltip(fn ($record) => $record->themes)->searchable()->sortable()

            ])
                ->filters([
                    // Filtro por estado del libro
                    Filter::make('status')
                        ->label('Estado')
                        ->form([
                            Forms\Components\Select::make('value')
                                ->label('Estado')
                                ->options([
                                    'disponible' => 'Disponible',
                                    'prestado' => 'Prestado',
                                    'reparacion' => 'En reparación',
                                    'retirado' => 'Retirado',
                                ])
                                ->native(false),
                        ])
                        ->query(function ($query, array $data) {
                            if ($data['value']) {
                                $query->where('status', $data['value']);
                            }
                        }),

                    // Filtro por año de publicación
                    Filter::make('rango_anios')
                        ->label('Filtrar por año de publicación')
                        ->form([
                            TextInput::make('desde')->label('Desde')->numeric(),
                            TextInput::make('hasta')->label('Hasta')->numeric(),
                        ])
                        ->query(function ($query, array $data) {
                            return $query
                                ->when($data['desde'], fn ($q) => $q->where('publishing_year', '>=', $data['desde']))
                                ->when($data['hasta'], fn ($q) => $q->where('publishing_year', '<=', $data['hasta']));
                        }),

                    // Filtro por editorial
                    Filter::make('publishingHouse_id')
                        ->label('Editorial')
                        ->form([
                            Forms\Components\Select::make('value')
                                ->label('Editorial')
                                ->relationship('publishingHouse', 'name')
                                ->searchable()
                                ->preload()
                                ->native(false),
                        ])
                        ->query(function ($query, array $data) {
                            if ($data['value']) {
                                $query->where('publishing_house_id', $data['value']);
                            }
                        }),

                    // Filtro por género
                    Filter::make('genre')
                        ->label('Género')
                        ->form([
                            Forms\Components\Select::make('value')
                                ->label('Género')
                                ->options(Genre::pluck('Género', 'id')) 
                                ->searchable()
                                ->preload()
                                ->native(false),
                        ])
                        ->query(function ($query, array $data) {
                            if ($data['value']) {
                                $query->whereHas('genres', fn ($q) => $q->where('genres.id', $data['value']));
                            }
                        }),

                    // Filtro por ubicación física
                    Filter::make('physic_location')
                        ->label('Ubicación')
                        ->form([
                            Forms\Components\TextInput::make('value')->label('Ubicación'),
                        ])
                        ->query(function ($query, array $data) {
                            if ($data['value']) {
                                $query->where('physic_location', 'like', "%{$data['value']}%");
                            }
                        }),

                    // Filtro por autor
                    Filter::make('author')
                        ->label('Autor')
                        ->form([
                            Forms\Components\Select::make('value')
                                ->label('Autor')
                                ->relationship('authors', fn ($query) => $query->selectRaw("CONCAT(name, ' ', lastname_1) AS full_name, id"))
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' ' . $record->lastname_1)
                                ->searchable()
                                ->preload()
                                ->native(false),
                        ])
                        ->query(function ($query, array $data) {
                            if ($data['value']) {
                                $query->whereHas('authors', fn ($q) => $q->where('id', $data['value']));
                            }
                        }),
                ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->modalHeading(fn ($record) => "Detalles del recurso bibliografico: " . $record->title),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('prestar')
                ->label('Prestar')
                ->icon('heroicon-m-book-open')
                ->url(fn (Book $record) => route('filament.admin.resources.loans.create', [
                    'book_id' => $record->id,
                ]))
                ->visible(fn (Book $record) => $record->status === 'disponible')
                ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('exportar_pdf')
            ->label('Exportar PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->action(function (\Illuminate\Support\Collection $records) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.listado', [
                    'books' => $records,
                ]);

        return response()->streamDownload(
            fn () => print($pdf->stream()),
            'listado_libros.pdf'
        );
    })
    ->deselectRecordsAfterCompletion(),

                ]),
            ])
            ->defaultSort('title') 
            ->recordUrl(null); 
    }

    public static function getRelations(): array
    {
        return [
            
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
