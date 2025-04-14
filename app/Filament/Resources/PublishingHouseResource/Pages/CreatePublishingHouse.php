<?php

namespace App\Filament\Resources\PublishingHouseResource\Pages;

use App\Filament\Resources\PublishingHouseResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePublishingHouse extends CreateRecord
{
    protected static string $resource = PublishingHouseResource::class;

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')->label('Regresar')->url($this->getResource()::getUrl());
    }
}
