<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca Virtual - Resultados de búsqueda</title>
    <link rel="icon" href="{{ asset('images/icono.ico') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-sky-200 p-6">
    <div class="max-w-4xl mx-auto bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-4">Resultados para: "{{ $query }}"</h1>
        <a href="{{ url('/search') }}" class="mt-6 inline-block text-blue-600 hover:underline">
            ← Regresar a búsqueda
        </a>

        {{-- Project Gutenberg Results --}}
        <h2 class="text-xl font-semibold mt-6 mb-4">Project Gutenberg</h2>
        @if($gutenberg->count() === 0)
            <div class="mb-4 p-4 border rounded bg-red-50 text-red-700">
                No se pudo obtener resultados de Project Gutenberg en este momento. Intente más tarde.
            </div>
        @endif
        @forelse($gutenberg as $book)
            <div class="mb-4 p-4 border rounded bg-gray-50">
                <a
                    href="{{ route('public-search.ver', ['fuente' => 'gutenberg', 'id' => $book['id'], 'url_texto' => urlencode($book['url_texto'])]) }}"
                    class="text-blue-700 text-lg font-semibold hover:underline"
                    target="_blank"
                >
                    {{ $book['titulo_traducido'] }}
                </a>
                <p class="text-xs text-gray-500 mt-1">Idioma: {{ strtoupper($book['idioma']) }}</p>
                <p class="text-sm italic text-gray-600">Título original: {{ $book['titulo_original'] }}</p>
                @if(!empty($book['autores']))
                    <p class="text-sm text-gray-800">Autor(es): {{ implode(', ', $book['autores']) }}</p>
                @endif
                <div>
                    <a
                        href="{{ route('public-search.ver', ['fuente' => 'gutenberg', 'id' => $book['id'], 'url_texto' => urlencode($book['url_texto'])]) }}"
                        class="text-blue-600 hover:underline"
                        target="_blank"
                    >
                        Ver texto
                    </a>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No se encontraron resultados en Project Gutenberg.</p>
        @endforelse

        {{-- Paginación --}}
        @if($gutenberg->hasPages())
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm text-blue-700 mb-2 font-medium">Navegación de Project Gutenberg</p>
                {{ $gutenberg->links('vendor.pagination.custom') }}
            </div>
        @endif

        {{-- PMC Results --}}
        <h2 class="text-xl font-semibold mt-6 mb-2">PubMed Central (PMC)</h2>
        @if($pmcResults->count() === 0)
            <div class="mb-4 p-4 border rounded bg-red-50 text-red-700">
                No se pudo obtener resultados de PubMed Central en este momento. Intente más tarde.
            </div>
        @endif
        @forelse($pmcResults as $pmc)
            <div class="mb-4 p-4 border rounded bg-gray-50">
                <a
                    href="{{ route('public-search.ver', ['fuente' => 'pmc', 'id' => $pmc['id']]) }}"
                    class="text-blue-700 text-lg font-semibold hover:underline"
                    target="_blank"
                >
                    {{ $pmc['titulo_traducido'] ?? $pmc['titulo'] }}
                </a>
            </div>
        @empty
            <p class="text-gray-500">No se encontraron resultados en PubMed Central.</p>
        @endforelse

        {{-- Paginación PMC --}}
        @if($pmcResults->hasPages())
            <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                {{ $pmcResults->links('vendor.pagination.custom') }}
            </div>
        @endif

        {{-- PubMed Results --}}
        <h2 class="text-xl font-semibold mt-6 mb-2">PubMed (NIH)</h2>
        @if($pubmedResults->count() === 0)
            <div class="mb-4 p-4 border rounded bg-red-50 text-red-700">
                No se pudo obtener resultados de PubMed en este momento. Intente más tarde.
            </div>
        @endif
        @forelse($pubmedResults as $pubmed)
            <div class="mb-4 p-4 border rounded bg-gray-50">
                <a
                    href="{{ route('public-search.ver', ['fuente' => 'pubmed', 'id' => $pubmed['id']]) }}"
                    class="text-blue-700 text-lg font-semibold hover:underline"
                    target="_blank"
                >
                    {{ $pubmed['titulo_traducido'] ?? $pubmed['titulo'] }}
                </a>
            </div>
        @empty
            <p class="text-gray-500">No se encontraron resultados en PubMed.</p>
        @endforelse

        {{-- Paginación PubMed --}}
        @if($pubmedResults->hasPages())
            <div class="mt-6 p-4 bg-purple-50 rounded-lg border border-purple-200"> 
                
                {{ $pubmedResults->links('vendor.pagination.custom') }}
            </div>
        @endif

        <a href="{{ url('/search') }}" class="mt-6 inline-block text-blue-600 hover:underline">
            ← Regresar a búsqueda
        </a>
    </div>

    <!-- Footer profesional -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-8">
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
                    Proyecto desarrollado por la <strong class="text-gray-800">Escuela Nacional de Enfermería de Cobán e INDAPSV</strong>
                </p>
                <p class="text-gray-500 text-xs mt-2">
                     2025 - Biblioteca Virtual ENEC
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Ocultar el loader si existe (cuando se navega desde la búsqueda)
        const loader = document.getElementById('loader');
        if (loader) {
            loader.classList.add('hidden');
        }

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', () => {
                const btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerText = 'Buscando... Por favor espere.';
                }
            });
        }
    </script>
</body>
</html>

