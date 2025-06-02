<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido</title>
    <link rel="icon" href="{{ asset('images/icono.ico') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-sky-200 flex flex-col items-center">

    <!-- Título superior -->
    <header class="w-full py-6 ">
        <h1 class="text-center text-4xl font-bold text-gray-800">Escuela Nacional de Enfermería de Cobán e INDAPSV</h1>
    </header>

    <!-- Contenido centrado -->
    <main class="flex-grow flex items-center justify-center w-full">
        <div class="text-center bg-white p-10 rounded-lg shadow-lg mt-10">
            <!-- Imagen -->
            <img src="{{ asset('images/logo2.png') }}" alt="Logo" class="mx-auto mb-6 w-32 h-32 object-contain">

            <!-- Subtítulo -->
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Bienvenido a Biblioteca</h2>

            <!-- Botón -->
            <a href="{{ url('/admin') }}" class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Gestion Bibliotecaria
            </a>
        </div>
    </main>

</body>
</html>

