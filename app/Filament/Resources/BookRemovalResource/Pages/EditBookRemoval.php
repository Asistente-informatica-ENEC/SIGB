<?php

namespace App\Filament\Resources\BookRemovalResource\Pages;

use App\Filament\Resources\BookRemovalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookRemoval extends EditRecord
{
    protected static string $resource = BookRemovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
