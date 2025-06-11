<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f0f0f0; }

        h2 { margin-top: 30px; margin-bottom: 10px; }

        .header { text-align: center; margin-bottom: 20px; }
        .logo { width: 80px; height: auto; margin-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 14px; margin-bottom: 5px; }
        .generated-date { font-size: 12px; color: #555; }

        .no-data { text-align: center; padding: 20px; font-style: italic; color: #a00; }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('logo1.png') }}" class="logo" alt="Logo Escuela">
        <div class="title">Escuela Nacional de Enfermería de Cobán e INDAPSV</div>
        <div class="subtitle">Reporte de Biblioteca</div>
        <div class="generated-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <h2>
        @switch($title)
            @case('book_inventory')
                📘 Reporte de Inventario de Libros
                @break
            @case('loan_history')
                📖 Reporte de Historial de Préstamos
                @break
            @case('active_loans')
                🔄 Reporte de Préstamos Activos
                @break
            @case('most_borrowed_books') 
                📈 Reporte de Libros Más Prestados
                @break
            @case('loans_by_period')
                🗓️ Reporte de Préstamos por Periodo
                @break
            @case('never_borrowed_books')
                📕 Reporte de Títulos Nunca Prestados
                @break
            @case('books_by_status')
                📚 Reporte de Libros por Estado
                @break
            @case('book_removals')
                🗑️ Reporte de Libros Retirados
                @break
            @default
                📄 {{ ucfirst(str_replace('_', ' ', $title)) }}
        @endswitch
    </h2>

    @if(count($data))
        <table>
            <thead>
                <tr>
                    <th>No.</th> <!-- Columna para numeración -->
                    @foreach(array_keys($data[0] ?? []) as $key)
                        <th>{{ $labels[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td> <!-- Número de fila -->
                        @foreach($row as $value)
                            <td>{{ $value !== null && $value !== '' ? $value : '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

    @else
        <div class="no-data">⚠️ No hay datos disponibles para este reporte.</div>
    @endif

</body>
</html>