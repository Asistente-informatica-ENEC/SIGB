<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        h2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>
    @switch($title)
        @case('book_inventory')
            📘 Reporte de Inventario de Libros
            @break
        @case('loan_history')
            📖 Reporte de historial de Préstamos
            @break
        @case('active_loans')
            🔄 Reporte de préstamos Activos
            @break
         @case('most_borrowed_books') 
            🔄 Reporte de libros mas prestados
            @break
        @case('loans_by_period')
            🔄 Reporte de prestamos por periodo
        @break
        @case('never_borrowed_books')
            🔄 Reporte de títulos nunca prestados
        @break
        @case('books_by_status')
            🔄 Reporte de libros por estado
        @break
        @default
            📄 {{ ucfirst(str_replace('_', ' ', $title)) }}
    @endswitch
</h2>

    <table>
        <thead>
            <tr>
                @foreach(array_keys($data[0] ?? []) as $key)
                    <th>{{ $labels[$key] ?? ucfirst($key) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    @foreach($row as $value)
                        <td>{{ $value ?? '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
