<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Biblioteca virtual - Búsqueda</title>
    <link rel="icon" href="{{ asset('images/icono.ico') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-sky-200 min-h-screen flex flex-col" style="background-image: url('{{ asset('images/fondo.jpeg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <!-- Loader oculto al inicio -->
    <div id="loader" class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-80 z-50 hidden">
        <div class="flex flex-col items-center">
            <svg class="animate-spin h-12 w-12 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span class="text-blue-700 font-semibold text-lg">Buscando recursos, por favor espere...</span>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="flex-grow flex items-center justify-center p-6">
        <div class="max-w-2xl w-full bg-white shadow-lg rounded-lg p-8">
            <!-- Logo -->
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo" class="mx-auto w-32 h-32 object-contain">
            </div>
            <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Buscar libro o recurso bibliográfico</h1>

            <form action="{{ url('/search/results') }}" method="GET">
                <div class="mb-6">
                    <label for="q" class="block text-sm font-medium text-gray-700 mb-2">Término de búsqueda:</label>
                    <input
                        type="text"
                        id="q"
                        name="q"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Ingresa el título, autor o tema del libro..."
                        required
                    >
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold shadow-md">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer profesional -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-6 mb-3">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Project Gutenberg</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">PMC (PubMed Central)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">PubMed</span>
                    </div>
                </div>
                <p class="text-gray-600 text-sm">
                     <strong class="text-gray-800">Escuela Nacional de Enfermería de Cobán e INDAPSV</strong>
                </p>
                <p class="text-gray-500 text-xs mt-2">
                    2025 - Biblioteca Virtual ENEC
                </p>
            </div>
        </div>
    </footer>
    <script>
        // Ocultar el loader si existe (cuando se navega con botón atrás)
        const loader = document.getElementById('loader');
        if (loader) {
            loader.classList.add('hidden');
        }

        const form = document.querySelector('form');
        if (form && loader) {
            form.addEventListener('submit', () => {
                loader.classList.remove('hidden');
            });
        }
    </script>
</body>
</html>

