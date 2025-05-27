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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookRemovalResource extends Resource
{
    protected static ?string $model = BookRemoval::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static ?string $navigationGroup = 'Gestión de Biblioteca';

       public static function getModelLabel(): string
    {
        return 'Retiro de recurso';
    }
    
    public static function getPluralModelLabel(): string
    {
        return 'Retiro de recursos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('book_id')
                    ->label('Libro')
                    ->relationship('book', 'title', fn ($query) => $query->disponible())
                    ->searchable()
                    ->preload()
                    ->required(),
                
                select::make('reason')
                ->label('Motivo de descarte')
                ->options([
                    'Daño Irreparable' => 'Daño irreparable',
                    'Pérdida' => 'Pérdida',
                    'Obsolencia' => 'Obsolencia',
                    'Otros' => 'Otros',
                ])
                ->required(),

                Textarea::make('observation')
                ->label('Observaciones')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(false),
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
            'edit' => Pages\EditBookRemoval::route('/{record}/edit'),
        ];
    }
    
}
