<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Historial de prestamos</title>
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
        <div class="subtitle">Historial de prestamos de Biblioteca</div>
        <div>Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Fecha de Préstamo</th>
                <th>Solicitante</th>
                <th>Título del Libro</th>
                <th>Fecha para Devolución</th>
                <th>Fecha de Devolución</th>
                <th>Estado</th>
                <th>Gestionado por</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loanHistories as $index => $history)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($history->loan_date)->format('d/m/Y H:i') }}</td>
                    <td>{{ $history->requester }}</td>
                    <td>{{ $history->book->title ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($history->return_date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $history->status }}</td>
                    <td>{{ $history->user->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
