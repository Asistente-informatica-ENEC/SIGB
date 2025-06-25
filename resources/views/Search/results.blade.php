<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca Virtual - Resultados de búsqueda</title>
    <link rel="icon" href="{{ asset('images/icono.ico') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-4">Resultados para: "{{ $query }}"</h1>

        {{-- Project Gutenberg Results --}}
        <h2 class="text-xl font-semibold mt-6 mb-4">Project Gutenberg</h2>
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
        <div class="mt-6">
            {{ $gutenberg->links() }}
        </div>

        {{-- PubMed Results --}}
        <h2 class="text-xl font-semibold mt-6 mb-2">PubMed (NIH)</h2>
        <ul class="list-disc list-inside space-y-2">
            @forelse($pubmedResults as $pubmed)
                <li class="mb-2">
                    <a
                        href="{{ route('public-search.ver', ['fuente' => 'pubmed', 'id' => $pubmed['id']]) }}"
                        class="text-blue-600 hover:underline font-semibold"
                        target="_blank"
                    >
                        {{ $pubmed['titulo_traducido'] ?? $pubmed['titulo'] }}
                    </a>
                </li>
            @empty
                <li class="text-gray-500">No se encontraron resultados en PubMed.</li>
            @endforelse
        </ul>

        <a href="{{ url('/search') }}" class="mt-6 inline-block text-blue-600 hover:underline">
            ← Regresar a búsqueda
        </a>
    </div>

    <script>
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

