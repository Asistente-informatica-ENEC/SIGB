<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookRemovalResource\Pages;
use App\Filament\Resources\BookRemovalResource\RelationManagers;
use App\Models\BookRemoval;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{Select, Textarea, Hidden};
use Filament\Tables\Columns\{TextColumn, BadgeColumn};
use Filament\Forms\Get;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookRemovalResource extends Resource
{
    protected static ?string $model = BookRemoval::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static ?string $navigationGroup = 'Gestión de Biblioteca';

       public static function getModelLabel(): string
    {
        return 'Retiros';
    }
    
    public static function getPluralModelLabel(): string
    {
        return 'Retiros';
    }

    public static function form(Form $form): Form
    {
    return $form
        ->schema([
            Hidden::make('loan_id')
            ->default(function (Get $get) {
                $bookId = $get('book_id');
                return \App\Models\Loan::where('book_id', $bookId)
                    ->where('status', 'prestado')
                    ->value('id');
            }),
            Select::make('book_id')
                ->label('Libro')
                ->relationship('book', 'title', fn ($query) => $query->whereIn('status', ['disponible', 'prestado']))
                ->searchable()
                ->preload()
                ->required()
                ->reactive()
                ->disabledOn('edit')
                ->afterStateUpdated(function ($state, callable $set) {
                    $book = \App\Models\Book::find($state);
                    $set('book_code', $book?->book_code ?? '');

                    if ($book && $book->status === 'prestado') {
                        Notification::make()
                            ->title('Advertencia')
                            ->body('Este libro se encuentra actualmente prestado. Asegúrate de que no será devuelto antes de darlo de baja.')
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),


            TextInput::make('book_code')
                ->label('Código del recurso')
                ->DisabledOn('edit')
                ->required()
                ->live(debounce: 500)
                ->afterStateUpdated(function ($state, callable $set) {
                    $book = \App\Models\Book::where('book_code', $state)->first();

                    if ($book) {
                        if ($book->status === 'disponible' || $book->status === 'prestado') {
                            $set('book_id', $book->id);
                        } else {
                            $set('book_id', null);

                            Notification::make()
                                ->title('Recurso no disponible')
                                ->body('El recurso está en estado "' . $book->status . '".')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    } else {
                        $set('book_id', null);

                        Notification::make()
                            ->title('Código inválido')
                            ->body('El código ingresado no corresponde a ningún recurso.')
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),



            Select::make('reason')
                ->label('Motivo de descarte')
                ->options([
                    'Daño Irreparable' => 'Daño irreparable',
                    'Pérdida' => 'Pérdida',
                    'Obsolencia' => 'Obsolencia',
                    'Otros' => 'Otros',
                ])
                ->disabledOn('edit')
                ->required(),

            Textarea::make('observation')
                ->label('Observaciones')
                ->disabledOn('edit')
                ->rows(3),

            Hidden::make('user_id')
                ->default(auth()->id()),

            Hidden::make('removed_at')
                ->default(now()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                ->label('Fecha de retiro')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
                TextColumn::make('book.title')->label('Libro'),
                TextColumn::make('book.book_code')->label('Código de recurso'),
                BadgeColumn::make('reason')->label('Motivo'),
                TextColumn::make('user.name')->label('Responsable'),
                TextColumn::make('observation')->label('Observaciones')->wrap(),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                
                
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
            'index' => Pages\ListBookRemovals::route('/'),
            'create' => Pages\CreateBookRemoval::route('/create'),
        ];
    }
    
}
