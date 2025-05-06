<?php

namespace App\Filament\Resources\LoanHistoryResource\Pages;

use App\Filament\Resources\LoanHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoanHistories extends ListRecords
{
    protected static string $resource = LoanHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
