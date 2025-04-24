<?php

namespace App\Filament\Resources\TypeCodeResource\Pages;

use App\Filament\Resources\TypeCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use EightyNine\ExcelImport\ExcelImportAction;

class ListTypeCodes extends ListRecords
{
    protected static string $resource = TypeCodeResource::class;

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
