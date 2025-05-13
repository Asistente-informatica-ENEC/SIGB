<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\RedirectResponse;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('cancel')
            ->label('Regresar')
            ->url($this->getResource()::getUrl());
    }

    protected function afterCreate(): RedirectResponse
    {
        return redirect($this->getResource()::getUrl('index'));
    }
}
