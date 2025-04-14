<?php

namespace App\Filament\Resources\TypeCodeResource\Pages;

use App\Filament\Resources\TypeCodeResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditTypeCode extends EditRecord
{
    protected static string $resource = TypeCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')->label('Regresar')->url($this->getResource()::getUrl());
    }

    protected function getCreatedRedirectUrl(): string
    {
        return $this->getResource()::getUrl();
    }

}
