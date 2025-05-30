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

        .header { text-align: center; margin-bottom: 20px; }
        .logo { width: 80px; height: auto; margin-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 14px; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('logo1.png') }}" class="logo" alt="Logo Escuela">
        <div class="title">Escuela Nacional de Enfermería de Cobán e INDAPSV</div>
        <div class="subtitle">Reporte de Biblioteca</div>
        <div>Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

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
            🔄 Reporte de libros más prestados
            @break
        @case('loans_by_period')
            🔄 Reporte de préstamos por periodo
            @break
        @case('never_borrowed_books')
            🔄 Reporte de títulos nunca prestados
            @break
        @case('books_by_status')
            🔄 Reporte de libros por estado
            @break
        @case('book_removals')
            🔄 Reporte de libros retirados
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

