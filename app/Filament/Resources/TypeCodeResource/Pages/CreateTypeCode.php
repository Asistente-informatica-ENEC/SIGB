<?php

namespace App\Filament\Resources\TypeCodeResource\Pages;

use App\Filament\Resources\TypeCodeResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateTypeCode extends CreateRecord
{
    protected static string $resource = TypeCodeResource::class;

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')->label('Regresar')->url($this->getResource()::getUrl());
    }

    protected function getCreatedRedirectUrl(): string
    {
        return $this->getResource()::getUrl();
    }

}
