<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca ENEC - Bienvenido</title>
    <link rel="icon" href="{{ asset('images/icono.ico') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-sky-200 flex flex-col items-center" style="background-image: url('{{ asset('images/fondo.jpeg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <!-- Título superior -->
    <header class="w-full py-6 ">
    <h1 class="text-center text-4xl font-bold text-gray-800 drop-shadow-[0_2px_2px_rgba(255,255,255,1)]" style="text-shadow: 2px 2px 0 #fff, -2px -2px 0 #fff, 2px -2px 0 #fff, -2px 2px 0 #fff;">
        Escuela Nacional de Enfermería de Cobán e INDAPSV
    </h1>
    </header>

    <!-- Contenido centrado -->
    <main class="flex-grow flex items-center justify-center w-full">
        <div class="text-center bg-white p-10 rounded-lg shadow-lg mt-10">
            <!-- Imagen -->
            <img src="{{ asset('images/logo2.png') }}" alt="Logo" class="mx-auto mb-6 w-32 h-32 object-contain">

            <!-- Subtítulo -->
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Bienvenido a Biblioteca</h2>

            <!-- Botones -->
            <div class="flex justify-center gap-4 mt-4">
                <a href="{{ url('/admin') }}" class="px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Gestion Bibliotecaria
                </a>
                <a href="{{ url('/search') }}" class="px-6 py-3 bg-green-600 text-white rounded hover:bg-green-700 transition">
                    Recursos Virtuales
                </a>
            </div>
        </div>
    </main>

</body>
</html>

