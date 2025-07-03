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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static ?string $navigationGroup = 'Gestión de Biblioteca';
    
    public static function getModelLabel(): string
    {
        return 'Recurso bibliográfico';
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
                                TextInput::make('name')->required()->label('Tipo de recurso')
                                            ->unique('type_codes', 'name', ignoreRecord: true)
                                                ->validationMessages([
                                            'unique' => 'Ya existe un tipo de recurso con ese nombre.',
                                        ]),
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
                                    TextInput::make('name')->required()->label('Nombre')
                                    ->unique('publishing_houses', 'name', ignoreRecord: true)
                                        ->validationMessages([
                                            'unique' => 'Ya existe una editorial con ese nombre.',
                                        ]),
                                    TextInput::make('location')->label('Localización')->required(),
                                ]) 
                ->searchable()
                ->preload()
                ->required(),
                TextInput::make('publishing_year')->label('Año de publicación')->required()->maxLength(255),
                TextInput::make('edition')->label('Edición')->required()->maxLength(255),
                Select::make('genres')
                    ->label('Temática/s')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->relationship('genres', 'Género')
                    ->createOptionForm([
                            TextInput::make('Género')->required()->label('Temática'),
                            ]) 
                    ->required(),
                TextInput::make('inventory_number')->label('Número de inventario')->required()->maxLength(255),
                TextInput::make('pages')->label('Páginas')->required()->maxLength(255),
                TextInput::make('physic_location')->label('Ubicación')->required()->maxLength(255),
                Textarea::make('themes')
                ->label('Temas')
                ->maxLength(8000)
                ->rows(10)
                ->columnSpan('full')
                ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->limit(42)
                ->tooltip(fn ($record) => $record->title)
                ->searchable()->sortable(),
                TextColumn::make('authors')
                    ->label('Autor/es')
                    ->limit(22)
                    ->getStateUsing(function ($record) {
                        return $record->authors
                            ->map(fn ($author) => $author->name . ' ' . $author->lastname_1)
                            ->join(', ');
                    })
                    ->tooltip(fn ($record) => $record->authors
                        ->map(fn ($author) => $author->name . ' ' . $author->lastname_1)
                        ->join(', '))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('authors', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('lastname_1', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('genres.Género')
                ->label('Temática/s')
                ->badge()
                ->separator(', ')
                ->limit(11) 
                ->tooltip(fn ($record) => $record->genres->pluck('Género')->join(', ')),
                TextColumn::make('publishingHouse.name')->label('Editorial')->limit(26)
                ->tooltip(fn ($record) => $record->publishingHouse?->name)
                ->searchable()->sortable(),
                TextColumn::make('publishing_year')->label('Año')->searchable()->sortable(),
                TextColumn::make('inventory_number')->label('No. Inventario')->searchable()->sortable(),
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

                TextColumn::make('edition')->label('Edición')->searchable()->sortable(),
                TextColumn::make('pages')->label('Páginas'),
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
                        ->label('Temática')
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
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\ViewAction::make()
                            ->color('info')
                            ->modalHeading(fn ($record) => "Detalles del recurso bibliografico: " . $record->title),
                        Tables\Actions\EditAction::make()
                            ->color('info'),
                        Tables\Actions\Action::make('duplicate')
                            ->label('Duplicar')
                            ->icon('heroicon-o-document-duplicate')
                            ->tooltip('Duplicar este recurso bibliográfico')
                            ->color('info')
                            ->action(function (Book $record) {
                                $duplicatedBook = $record->replicate();
                                $duplicatedBook->title = $record->title;
                                $duplicatedBook->book_code = $record->book_code;
                                $duplicatedBook->inventory_number = 'Ingrese nuevo número de inventario';
                                $duplicatedBook->status = 'disponible';

                                $duplicatedBook->save();

                                $duplicatedBook->authors()->sync($record->authors->pluck('id'));
                                $duplicatedBook->genres()->sync($record->genres->pluck('id'));

                                return redirect()->route('filament.admin.resources.books.edit', ['record' => $duplicatedBook->id]);
                            }),
                        Tables\Actions\Action::make('prestar')
                            ->label('Prestar')
                            ->color('info')
                            ->icon('heroicon-m-book-open')
                            ->url(fn (Book $record) => route('filament.admin.resources.loans.create', [
                                'book_id' => $record->id,
                            ]))
                            ->visible(fn (Book $record) => $record->status === 'disponible')
                            ->openUrlInNewTab(),
                        Tables\Actions\DeleteAction::make()
                            ->before(function (Book $record) {
                                if ($record->loans()->exists() || $record->loanHistories()->exists()) {
                                    Notification::make() // Ya no necesitas la barra invertida si importas Notification
                                        ->title('No se puede eliminar')
                                        ->body('Este recurso está asociado a préstamos o historial y no puede ser eliminado.')
                                        ->danger()
                                        ->send();

                                    // Lanza una excepción para detener la acción de eliminación
                                    throw ValidationException::withMessages([
                                        'delete' => 'El recurso no puede ser eliminado debido a asociaciones existentes.',
                                    ]);
                                }
                                // No es necesario un 'return true;' explícito aquí,
                                // si no se lanza la excepción, la acción continuará.
                            }),
                    ])
                ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
            ->before(function (Collection $records) { // Asegúrate de tipar $records como Collection
                $blockedTitles = [];
                $deletableRecords = new Collection(); // Colección para guardar los registros que sí se pueden eliminar

                foreach ($records as $record) {
                    if ($record->loans()->exists() || $record->loanHistories()->exists()) {
                        $blockedTitles[] = $record->title;
                    } else {
                        $deletableRecords->add($record); // Agrega a los que sí se pueden eliminar
                    }
                }

                if (!empty($blockedTitles)) {
                    Notification::make()
                        ->title('Eliminación parcial cancelada')
                        ->body('No se eliminaron los siguientes registros porque están asociados a préstamos o historial: ' . implode(', ', $blockedTitles))
                        ->danger()
                        ->send();
                }

                // Si todos los registros estaban bloqueados, lanzamos una excepción
                // para detener completamente la acción masiva y evitar que intente eliminar nada.
                if ($deletableRecords->isEmpty() && !empty($blockedTitles)) {
                    throw ValidationException::withMessages([
                        'bulk_delete' => 'Ninguno de los recursos seleccionados puede ser eliminado debido a asociaciones existentes.',
                    ]);
                }

                // Importante: Devuelve solo los registros que realmente se pueden eliminar.
                // Filament usará esta colección para la operación de borrado.
                return $deletableRecords;
            }),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', '!=', 'retirado');
    }
}
