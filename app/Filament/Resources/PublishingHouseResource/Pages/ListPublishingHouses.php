<?php

namespace App\Filament\Resources\PublishingHouseResource\Pages;

use App\Filament\Resources\PublishingHouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use EightyNine\ExcelImport\ExcelImportAction;

class ListPublishingHouses extends ListRecords
{
    protected static string $resource = PublishingHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
            ->color("primary")
            ->label('Importar'),
            Actions\CreateAction::make(),
        ];
    }
}
