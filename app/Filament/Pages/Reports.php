<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanHistory;
use App\Models\BookRemoval;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Livewire\Component;  
use Livewire\Attributes\On;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $title = 'Reportes';
    protected static string $view = 'filament.pages.report';
    protected static ?string $navigationGroup = 'Sistema';

    public string $reportType = '';
    public string $bookStatus = '';
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
            'loans_by_period' => Loan::with(['book', 'user'])
                ->whereBetween('loan_date', [$this->startDate, $this->endDate])
                ->get()
                ->map(function ($loan) {
                    return [
                        'Título del Libro' => $loan->book->title ?? 'N/A',
                        'Código' => $loan->book->book_code ?? 'N/A',
                        'Solicitante' => $loan->requester,
                        'Fecha de Préstamo' => \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y H:i'),
                        'Fecha para Devolución' => \Carbon\Carbon::parse($loan->return_date)->format('d/m/Y'),
                        'Fecha de Registro' => \Carbon\Carbon::parse($loan->created_at)->format('d/m/Y H:i'),
                        'Bibliotecario' => $loan->user->name ?? 'N/A',
                    ];
                }),

            'most_borrowed_books' => LoanHistory::selectRaw('book_id, COUNT(*) as total')
                ->groupBy('book_id')
                ->with('book')
                ->orderByDesc('total')
                ->get()
                ->map(function ($loanHistory) {
                    return [
                        'Título del Libro' => $loanHistory->book->title ?? 'Desconocido',
                        'Veces Prestado' => $loanHistory->total,
                    ];
                }),

            'never_borrowed_books' => Book::whereDoesntHave('loans')
                ->with(['authors', 'PublishingHouse'])
                ->get()
                ->map(function ($book) {
                    return [
                        'Título' => $book->title,
                        'Autor/es' => $book->authors->map(fn($author) => $author->name . ' ' . $author->lastname_1)->join(', '),
                        'Código' => $book->book_code,
                        'Año' => $book->publishing_year,
                        'Editorial' => $book->PublishingHouse->name ?? 'Sin editorial',
                        'No. Inventario' => $book->inventory_number,
                        'Ubicación' => $book->physic_location,
                    ];
                }),

            'active_loans' => Loan::with(['book', 'user'])
                ->get()
                ->map(function ($loan) {
                    return [
                        'Título del Libro' => $loan->book->title ?? 'N/A',
                        'Código' => $loan->book->book_code ?? 'N/A',
                        'Solicitante' => $loan->requester,
                        'Fecha de Préstamo' => \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y H:i'),
                        'Fecha para Devolución' => \Carbon\Carbon::parse($loan->return_date)->format('d/m/Y'),
                        'Bibliotecario' => $loan->user->name ?? 'N/A',
                    ];
                }),

            'loan_history' => LoanHistory::with(['book', 'user'])
                ->whereBetween('loan_date', [$this->startDate, $this->endDate])
                ->get()
                ->map(function ($loanHistory) {
                    return [
                        'Título del Libro' => $loanHistory->book->title ?? 'Desconocido',
                        'Solicitante' => $loanHistory->requester,
                        'Código de recurso' => $loanHistory->book->book_code,
                        'Fecha de Préstamo' => \Carbon\Carbon::parse($loanHistory->loan_date)->format('d/m/Y H:i'),
                        'Fecha para Devolución' => \Carbon\Carbon::parse($loanHistory->return_date)->format('d/m/Y'),
                        'Fecha de Devolución' => \Carbon\Carbon::parse($loanHistory->updated_at)->format('d/m/Y H:i'),
                        'Gestionado por' => $loanHistory->user->name ?? 'Desconocido',
                    ];
                }),

            'book_inventory' => Book::with(['authors', 'PublishingHouse'])
                ->get()
                ->map(function ($book) {
                    return [
                        'Título' => $book->title,
                        'Autor/es' => $book->authors->map(fn($author) => $author->name . ' ' . $author->lastname_1)->join(', '),
                        'Código' => $book->book_code,
                        'Año' => $book->publishing_year,
                        'Editorial' => $book->PublishingHouse->name ?? 'Sin editorial',
                        'Estado' => ucfirst($book->status),
                        'No. Inventario' => $book->inventory_number,
                        'Ubicación' => $book->physic_location,
                    ];
                }),

                'book_removals' => BookRemoval::with(['book', 'user'])
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->get()
                ->map(function ($removal) {
                    return [
                        'Fecha de Retiro' => \Carbon\Carbon::parse($removal->created_at)->format('d/m/Y H:i'),
                        'Título del Libro' => $removal->book->title ?? 'Desconocido',
                        'Código del recurso' => $removal->book->book_code ?? 'N/A',
                        'Motivo' => $removal->reason,
                        'Observaciones' => $removal->observation ?? '',
                        'Gestionado por' => $removal->user->name ?? 'N/A',
                    ];
                }),

            'books_by_status' => (function () {
                $query = Book::query()->with(['authors', 'PublishingHouse']);

                if (!empty($this->bookStatus)) {
                    $query->where('status', $this->bookStatus);
                }

                return $query->get()->map(function ($book) {
                    return [
                        'Título' => $book->title,
                        'Autor/es' => $book->authors->map(fn($author) => $author->name . ' ' . $author->lastname_1)->join(', '),
                        'Código' => $book->book_code,
                        'Año' => $book->publishing_year,
                        'Editorial' => $book->PublishingHouse->name ?? 'Sin editorial',
                        'No. Inventario' => $book->inventory_number,
                        'Ubicación' => $book->physic_location,
                    ];
                });
            })(),

            default => collect(),
        };

}
    protected function paginateCollection($items, $perPage = 10)
    {
        $page = request()->get('page', 1);
        $items = collect($items);
        $currentItems = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
    
    
    public function exportToPdf()
    {
        $data = $this->results->toArray();
        $title = $this->reportType ?? 'Reporte';
        $columnLabels = [
            'id' => 'ID',
            'title' => 'Título',
            'author_id' => 'Autor',
            'status' => 'Estado',
            'type_code_id' => 'Tipo de recurso',
            'book_code' => 'Código',
            'loan_date' => 'Fecha de Préstamo',
            'return_date' => 'Fecha de Devolución',
            'created_at' => 'Fecha de Registro',
            'updated_at' => 'Última Actualización',
            'book_id' => 'Libro',
            'edition' => 'Edición',
            'inventory_number' => 'No. Inventario',
            'requester' => 'Solicitante',
            'user_id' => 'Registrado por',
            'physic_location' => 'Ubicación',
            'themes' => 'Temas',
            'name' => 'Nombre',
            'publishing_house_id' => 'Editorial',
            'publishing_year' => 'Año de publicación',
            'genre_id' => 'Temática',
            'total' => 'Total de Préstamos',
            'reason' => 'Motivo',
            'observation' => 'Observaciones',
            ];

        $pdf = Pdf::loadView('reports.pdf', [
            'title' => $title,
            'data' => $data,
            'labels' => $columnLabels,
        ]);

        return response()->streamDownload(
            fn () => print($pdf->stream()),
            'reporte_' . now()->format('Ymd_His') . '.pdf'
        );
    }
}
