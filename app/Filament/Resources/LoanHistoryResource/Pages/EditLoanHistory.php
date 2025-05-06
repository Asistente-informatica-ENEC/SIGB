<?php

namespace App\Filament\Resources\LoanHistoryResource\Pages;

use App\Filament\Resources\LoanHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoanHistory extends EditRecord
{
    protected static string $resource = LoanHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
