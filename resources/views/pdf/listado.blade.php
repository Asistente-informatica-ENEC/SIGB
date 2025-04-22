<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Listado de Libros</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Listado de Libros Seleccionados</h2>

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
