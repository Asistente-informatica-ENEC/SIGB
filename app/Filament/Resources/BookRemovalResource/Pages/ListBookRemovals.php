<?php

namespace App\Filament\Resources\BookRemovalResource\Pages;

use App\Filament\Resources\BookRemovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookRemovals extends ListRecords
{
    protected static string $resource = BookRemovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
