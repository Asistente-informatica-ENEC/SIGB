<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use App\Models\Book;
use Filament\Actions;
use Filament\Support\Contracts\HasLabel;
use Filament\Resources\Pages\CreateRecord;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (request()->has('book_id')) {
            $data['book_id'] = request()->get('book_id');
        }

        $data['user_id'] = auth()->id(); 
        $data['loan_date'] = now();
        return $data;
    }

    protected function beforeCreate(): void
    {
        // Cambiar el estado del libro a 'prestado'
        $bookId = $this->data['book_id'];

        Book::where('id', $bookId)->update([
            'status' => 'prestado',
        ]);
    }

    protected function getCreatedRedirectUrl(): string
    {
        return LoanResource::getUrl(); 

    }
}
