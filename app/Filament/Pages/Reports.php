<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanHistory;
use Illuminate\Support\Collection;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $title = 'Reportes';
    protected static string $view = 'filament.pages.report';
    protected static ?string $navigationGroup = 'Sistema';

    public string $reportType = '';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public Collection $results;

    public function mount(): void
    {
        $this->results = collect();
    }

    public function generateReport()
    {
        $this->results = match ($this->reportType) {
            'loans_by_period' => Loan::with('book')
                ->whereBetween('loan_date', [$this->startDate, $this->endDate])
                ->get(),

            'most_borrowed_books' => Loan::selectRaw('book_id, COUNT(*) as total')
                ->groupBy('book_id')
                ->with('book')
                ->orderByDesc('total')
                ->get(),

            'never_borrowed_books' => Book::whereDoesntHave('loans')->get(),

            'active_loans' => Loan::with('book')->get(),

            'loan_history' => LoanHistory::with('book')->whereBetween('loan_date', [$this->startDate, $this->endDate])->get(),

            default => collect(),
        };
    }
}

