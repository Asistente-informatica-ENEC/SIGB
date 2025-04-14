<?php

namespace App\Filament\Resources\PublishingHouseResource\Pages;

use App\Filament\Resources\PublishingHouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPublishingHouses extends ListRecords
{
    protected static string $resource = PublishingHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
