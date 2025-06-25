<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Biblioteca virtual - Búsqueda</title>
    <link rel="icon" href="{{ asset('images/icono.ico') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-4">Buscar libro o recurso bibliográfico</h1>

        <form action="{{ url('/search/results') }}" method="GET">
            <input
                type="text"
                name="q"
                placeholder="Ingrese título, autor, o palabra clave"
                class="w-full border p-3 rounded mb-4"
                required
            >
            <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
            >
                Buscar
            </button>
        </form>
    </div>
</body>
</html>

