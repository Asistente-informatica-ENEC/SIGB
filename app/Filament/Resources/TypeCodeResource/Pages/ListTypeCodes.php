<?php

namespace App\Filament\Resources\TypeCodeResource\Pages;

use App\Filament\Resources\TypeCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeCodes extends ListRecords
{
    protected static string $resource = TypeCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
