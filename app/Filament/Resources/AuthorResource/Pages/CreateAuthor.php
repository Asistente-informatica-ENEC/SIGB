<?php

namespace App\Filament\Resources\AuthorResource\Pages;

use App\Filament\Resources\AuthorResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAuthor extends CreateRecord
{
    protected static string $resource = AuthorResource::class;

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Regresar')
            ->url($this->getResource()::getUrl());
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = $this->getModel()::create($data);

        // Redirige inmediatamente al listado sin pasar por el formulario de edición
        $this->redirect($this->getResource()::getUrl());

        return $record;
    }
}

