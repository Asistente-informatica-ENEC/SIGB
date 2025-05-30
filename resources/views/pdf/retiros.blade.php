<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Listado de retiros</title>
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
        <div class="subtitle">Listado de Libros retirados de Biblioteca</div>
        <div>Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Fecha de retiro</th>
                <th>Titulo</th>
                <th>Código del recurso</th>
                <th>Motivo</th>
                <th>Observaciónes</th>
                <th>Gestionado por:</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookremovals as $index => $bookremoval)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($bookremoval->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $bookremoval->book->title }}</td>
                    <td>{{ $bookremoval->book?->book_code }}</td>
                    <td>{{ $bookremoval->reason }}</td>
                    <td>{{ $bookremoval->observation }}</td>
                    <td>{{ $bookremoval->user->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
