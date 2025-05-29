<?php

namespace App\Filament\Resources\BookRemovalResource\Pages;

use App\Filament\Resources\BookRemovalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;

class EditBookRemoval extends EditRecord
{
    protected static string $resource = BookRemovalResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
