<?php

namespace App\Filament\Resources\PublishingHouseResource\Pages;

use App\Filament\Resources\PublishingHouseResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPublishingHouse extends EditRecord
{
    protected static string $resource = PublishingHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')->label('Regresar')->url($this->getResource()::getUrl());
    }
}
