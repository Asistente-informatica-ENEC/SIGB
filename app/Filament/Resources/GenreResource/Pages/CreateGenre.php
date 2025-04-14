<?php

namespace App\Filament\Resources\GenreResource\Pages;

use App\Filament\Resources\GenreResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGenre extends CreateRecord
{
    protected static string $resource = GenreResource::class;

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('cancel')
            ->label('Regresar')
            ->url($this->getResource()::getUrl());
    }
}
