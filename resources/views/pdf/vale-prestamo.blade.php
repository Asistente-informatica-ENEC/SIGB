<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vales de Préstamo</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 20px; }
        .header { text-align: center; margin-bottom: 10px; }
        .logo { height: 50px; }
        .title { font-size: 14px; font-weight: bold; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #000; }
        th, td { padding: 6px; text-align: left; border: 1px solid #000; vertical-align: top; }
        .procedencia-list { list-style: none; padding-left: 0; margin: 0; columns: 2; }
    </style>
</head>
<body>

@foreach ($loans as $index => $loan)
    <div style="{{ $index < count($loans) - 1 ? 'page-break-after: always;' : '' }}">
        <div class="header">
            <img src="{{ public_path('logo1.png') }}" class="logo" alt="Logo Escuela">
            <div class="title">Vale de Préstamo Bibliográfico</div>
            <p>Fecha: {{ now()->format('d/m/Y') }}</p>
        </div>

        <table>
            <tr>
                <th>Solicitante:</th>
                <td colspan="3">{{ $loan->requester }}</td>
            </tr>
            <tr>
                <th>Recurso Bibliográfico:</th>
                <td colspan="3">{{ $loan->book->title }}</td>
            </tr>
            <tr>
                <th colspan="4">Procedencia Académica:</th>
            </tr>
            <tr>
                <td colspan="4">
                    @php
                        $opcionesProcedencia = [
                            'Técnico en Enfermería - 1er año',
                            'Técnico en Enfermería - 2do año',
                            'Técnico en Enfermería - 3er año',
                            'Licenciatura en Enfermería - 4to año',
                            'Licenciatura en Enfermería - 5to año',
                            'Auxiliares de Enfermería - A',
                            'Auxiliares de Enfermería - B',
                            'Auxiliares de Enfermería - C',
                            'Auxiliares de Enfermería - D',
                            'Laboratorio Clínico',
                            'Personal',
                            'Externo',
                        ];
                        $seleccionada = $loan->procedencia;
                    @endphp

                    <ul class="procedencia-list">
                        @foreach ($opcionesProcedencia as $opcion)
                            <li>{!! $opcion === $seleccionada ? '◉' : '○' !!} {{ $opcion }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            <tr>
                <th>Fecha del Préstamo:</th>
                <td>{{ $loan->loan_date }}</td>
                <th>Fecha de Devolución:</th>
                <td>{{ $loan->return_date }}</td>
            </tr>
            <tr>
                <th colspan="4">Firma del solicitante:</th>
            </tr>
            <tr>
                <td colspan="4" style="height: 40px;"></td>
            </tr>
        </table>
    </div>
@endforeach

</body>
</html>


