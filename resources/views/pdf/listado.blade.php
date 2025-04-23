<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Listado de Libros</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 10px; }
        .logo { height: 60px; }
        .title { font-size: 16px; font-weight: bold; margin-top: 10px; }
        .subtitle { font-size: 14px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('logo1.png') }}" class="logo" alt="Logo Escuela">
        <div class="title">Escuela Nacional de Enfermería de Cobán e INDAPSV</div>
        <div class="subtitle">Listado de Libros de la Biblioteca</div>
        <div>Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Título</th>
                <th>Autor/es</th>
                <th>Código</th>
                <th>Año</th>
                <th>Editorial</th>
                <th>No. Inventario</th>
                <th>Ubicación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($books as $index => $book)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $book->title }}</td>
                    <td>
                        {{ $book->authors->map(fn($a) => $a->name . ' ' . $a->lastname_1)->join(', ') }}
                    </td>
                    <td>{{ $book->typeCode->name. '-'.$book->book_code }}</td>
                    <td>{{ $book->publishing_year }}</td>
                    <td>{{ $book->publishingHouse->name }}</td>
                    <td>{{ $book->inventory_number }}</td>
                    <td>{{ $book->physic_location }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
