<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Contenido del recurso</title>
    <link rel="icon" href="{{ asset('images/icono.ico') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-sky-200 min-h-screen flex flex-col">
    <div class="flex-grow flex justify-center items-center p-6">
        <div class="bg-white shadow-lg rounded-lg max-w-5xl w-full max-h-[80vh] overflow-y-auto p-6 font-serif text-gray-900 leading-relaxed text-base">
            <h1 class="text-3xl font-bold mb-6 border-b pb-3">
                {{ isset($titulo) && $titulo ? $titulo : 'Contenido del recurso' }}
            </h1>

            {{-- Metadatos del artículo PubMed y Project Gutenberg --}}
            @if(isset($titulo_traducido) || isset($doi) || isset($autores) || isset($year) || isset($institucion) || isset($temas) || isset($idioma))
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <ul class="text-base text-gray-800 space-y-1">
                        @if(!empty($titulo_traducido))
                            <li><span class="font-semibold">Título (español):</span> {{ $titulo_traducido }}</li>
                        @endif
                        @if(!empty($doi))
                            <li><span class="font-semibold">DOI:</span> <a href="https://doi.org/{{ $doi }}" class="text-blue-700 underline" target="_blank">{{ $doi }}</a></li>
                        @endif
                        @if(!empty($autores))
                            <li><span class="font-semibold">Autor(es):</span> {{ is_array($autores) ? implode(', ', $autores) : $autores }}</li>
                        @endif
                        @if(!empty($year))
                            <li><span class="font-semibold">Año de publicación:</span> {{ $year }}</li>
                        @endif
                        @if(!empty($institucion))
                            <li><span class="font-semibold">Institución:</span> {{ $institucion }}</li>
                        @endif
                        {{-- Metadatos específicos de Project Gutenberg --}}
                        @if(!empty($temas))
                            <li><span class="font-semibold">Temas:</span> {{ is_array($temas) ? implode(', ', $temas) : $temas }}</li>
                        @endif
                        @if(!empty($idioma))
                            <li><span class="font-semibold">Idioma:</span> {{ strtoupper($idioma) }}</li>
                        @endif
                    </ul>
                </div>
            @endif

            {{-- Controles de traducción --}}
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-blue-800">Opciones de traducción</h3>
                    <div class="flex space-x-2">
                        <button id="btn-traducir-todo" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                            Traducir
                        </button>
                        <button id="btn-reset" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                            Mostrar original
                        </button>
                    </div>
                </div>
                
                {{-- Barra de progreso (oculta por defecto) --}}
                <div id="progress-container" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Traduciendo...</span>
                        <span id="progress-text" class="text-sm font-medium text-gray-700">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="progress-bar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="progress-message" class="text-sm text-gray-600 mt-2">Iniciando traducción...</p>
                </div>
            </div>

            {{-- Contenido del texto --}}
            <div id="content-container">
                @if(!empty($contenido))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-green-800 font-medium">Contenido cargado correctamente</span>
                        </div>
                    </div>
                    <div id="texto-original" class="whitespace-pre-wrap break-words text-justify shadow">{{ $contenido }}</div>
                    <div id="texto-traducido" class="whitespace-pre-wrap break-words hidden text-justify shadow">{{ $contenido }}</div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">No se pudo cargar el contenido del recurso.</p>
                        <a href="javascript:history.back()" class="mt-4 inline-block text-blue-600 hover:underline">← Volver a los resultados</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Botón flotante para traducción por selección --}}
    <div id="floating-translate-btn" class="fixed bottom-6 right-6 z-50 hidden">
        <div class="bg-green-600 text-white rounded-full p-4 shadow-lg hover:bg-green-700 transition-all duration-300 cursor-pointer group">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
            </svg>
            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <span id="selection-length">0</span>
            </div>
        </div>
        
        {{-- Tooltip --}}
        <div class="absolute bottom-full right-0 mb-2 bg-gray-800 text-white text-sm rounded px-3 py-2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
            Traducir selección
            <div class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
        </div>
    </div>

    {{-- Modal de progreso para traducción por selección --}}
    <div id="selection-progress-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800">Traduciendo selección</h3>
            </div>
            
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progreso</span>
                    <span id="selection-progress-text" class="text-sm font-medium text-gray-700">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="selection-progress-bar" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <p id="selection-progress-message" class="text-sm text-gray-600">Iniciando traducción...</p>
            
            <div class="mt-6 flex justify-end">
                <button id="cancel-selection-translate" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnTraducirTodo = document.getElementById('btn-traducir-todo');
            const btnReset = document.getElementById('btn-reset');
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const progressMessage = document.getElementById('progress-message');
            const textoOriginal = document.getElementById('texto-original');
            const textoTraducido = document.getElementById('texto-traducido');
            
            // Elementos del botón flotante
            const floatingBtn = document.getElementById('floating-translate-btn');
            const selectionLength = document.getElementById('selection-length');
            const selectionProgressModal = document.getElementById('selection-progress-modal');
            const selectionProgressBar = document.getElementById('selection-progress-bar');
            const selectionProgressText = document.getElementById('selection-progress-text');
            const selectionProgressMessage = document.getElementById('selection-progress-message');
            const cancelSelectionTranslate = document.getElementById('cancel-selection-translate');
            
            const textoCompleto = textoOriginal.textContent;
            let traduccionCompleta = '';
            let traduccionEnProgreso = false;
            let seleccionEnProgreso = false;
            let cancelarSeleccion = false;

            // Mostrar/ocultar botón flotante según selección
            document.addEventListener('selectionchange', function() {
                const seleccion = window.getSelection();
                const textoSeleccionado = seleccion.toString().trim();
                
                if (textoSeleccionado.length > 0) {
                    floatingBtn.classList.remove('hidden');
                    selectionLength.textContent = textoSeleccionado.length;
                } else {
                    floatingBtn.classList.add('hidden');
                }
            });

            // Traducir todo el texto
            btnTraducirTodo.addEventListener('click', async function() {
                if (traduccionEnProgreso) return;
                
                traduccionEnProgreso = true;
                progressContainer.classList.remove('hidden');
                btnTraducirTodo.disabled = true;
                
                const chunkSize = 500;
                const totalChunks = Math.ceil(textoCompleto.length / chunkSize);
                traduccionCompleta = '';
                
                for (let i = 0; i < totalChunks; i++) {
                    const inicio = i * chunkSize;
                    const fin = Math.min((i + 1) * chunkSize, textoCompleto.length);
                    
                    try {
                        const response = await fetch('{{ route("public-search.traducir") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                texto: textoCompleto,
                                inicio: inicio,
                                fin: fin
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.error) {
                            throw new Error(data.error);
                        }
                        
                        traduccionCompleta += data.traducido + ' ';
                        
                        // Actualizar progreso
                        const progreso = Math.round(((i + 1) / totalChunks) * 100);
                        progressBar.style.width = progreso + '%';
                        progressText.textContent = progreso + '%';
                        progressMessage.textContent = `Traduciendo fragmento ${i + 1} de ${totalChunks}...`;
                        
                        // Pequeña pausa para no sobrecargar
                        await new Promise(resolve => setTimeout(resolve, 100));
                        
                    } catch (error) {
                        console.error('Error al traducir:', error);
                        progressMessage.textContent = 'Error en la traducción: ' + error.message;
                        break;
                    }
                }
                
                // Mostrar resultado
                textoOriginal.classList.add('hidden');
                textoTraducido.textContent = traduccionCompleta.trim();
                textoTraducido.classList.remove('hidden');
                progressContainer.classList.add('hidden');
                
                btnTraducirTodo.disabled = false;
                traduccionEnProgreso = false;
            });

            // Traducir selección (botón flotante)
            floatingBtn.addEventListener('click', async function() {
                const seleccion = window.getSelection();
                const textoSeleccionado = seleccion.toString().trim();
                
                if (!textoSeleccionado) {
                    alert('Por favor selecciona el texto que quieres traducir');
                    return;
                }
                
                if (seleccionEnProgreso) return;
                
                seleccionEnProgreso = true;
                cancelarSeleccion = false;
                selectionProgressModal.classList.remove('hidden');
                floatingBtn.classList.add('hidden');
                
                try {
                    // Simular progreso para la selección
                    let progreso = 0;
                    const interval = setInterval(() => {
                        if (cancelarSeleccion) {
                            clearInterval(interval);
                            return;
                        }
                        
                        progreso += 10;
                        selectionProgressBar.style.width = progreso + '%';
                        selectionProgressText.textContent = progreso + '%';
                        
                        if (progreso >= 100) {
                            clearInterval(interval);
                        }
                    }, 100);
                    
                    selectionProgressMessage.textContent = 'Analizando texto seleccionado...';
                    
                    const response = await fetch('{{ route("public-search.traducir") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            texto: textoSeleccionado,
                            inicio: 0,
                            fin: textoSeleccionado.length
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Reemplazar el texto seleccionado con la traducción
                    const range = seleccion.getRangeAt(0);
                    range.deleteContents();
                    range.insertNode(document.createTextNode(data.traducido));
                    
                    // Completar progreso
                    selectionProgressBar.style.width = '100%';
                    selectionProgressText.textContent = '100%';
                    selectionProgressMessage.textContent = 'Traducción completada';
                    
                    setTimeout(() => {
                        selectionProgressModal.classList.add('hidden');
                        seleccionEnProgreso = false;
                    }, 1000);
                    
                } catch (error) {
                    console.error('Error al traducir:', error);
                    selectionProgressMessage.textContent = 'Error en la traducción: ' + error.message;
                    setTimeout(() => {
                        selectionProgressModal.classList.add('hidden');
                        seleccionEnProgreso = false;
                    }, 2000);
                }
            });

            // Cancelar traducción de selección
            cancelSelectionTranslate.addEventListener('click', function() {
                cancelarSeleccion = true;
                selectionProgressModal.classList.add('hidden');
                seleccionEnProgreso = false;
            });

            // Mostrar texto original
            btnReset.addEventListener('click', function() {
                textoOriginal.classList.remove('hidden');
                textoTraducido.classList.add('hidden');
                progressContainer.classList.add('hidden');
                traduccionCompleta = '';
            });

            const btnTraducirTitulo = document.getElementById('btn-traducir-titulo');
            const tituloArticulo = document.getElementById('titulo-articulo');
            if (btnTraducirTitulo && tituloArticulo) {
                btnTraducirTitulo.addEventListener('click', async function() {
                    btnTraducirTitulo.disabled = true;
                    btnTraducirTitulo.innerText = 'Traduciendo...';
                    try {
                        const response = await fetch("{{ route('public-search.traducir') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                texto: tituloArticulo.textContent,
                                inicio: 0,
                                fin: tituloArticulo.textContent.length
                            })
                        });
                        const data = await response.json();
                        if (data.traducido) {
                            tituloArticulo.textContent = data.traducido;
                        }
                    } catch (error) {
                        alert('Error al traducir el título');
                    }
                    btnTraducirTitulo.disabled = false;
                    btnTraducirTitulo.innerText = 'Traducir';
                });
            }
        });
    </script>



</body>
</html>
