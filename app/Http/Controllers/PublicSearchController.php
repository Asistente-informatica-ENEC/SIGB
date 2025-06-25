<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class PublicSearchController extends Controller
{
    public function buscar()
    {
        return view('search.search');
    }

    public function resultados(Request $request)
    {
        $query = $request->input('q');
        $lang = $request->input('lang', 'es'); // idioma destino, por defecto español

        // Traducción del término original (español) a varios idiomas para Gutenberg
        $idiomas = ['en', 'de', 'fr', 'ru', 'pt'];
        $traducciones = [];

        foreach ($idiomas as $idioma) {
            $response = Http::timeout(20)->post('http://localhost:5001/translate', [
                'q' => $query,
                'source' => 'es',
                'target' => $idioma,
                'format' => 'text',
            ]);

            $traducciones[$idioma] = $response->json()['translatedText'] ?? null;
        }

        // Construir array de términos para buscar en Gutenberg (es + traducciones)
        $terminos = array_filter(array_merge([$query], $traducciones));

        // Buscar en Project Gutenberg usando todos los términos
        $gutenberg = [];
        foreach ($terminos as $termino) {
            try {
                $result = Http::timeout(40)->get("https://gutendex.com/books", [
                    'search' => $termino,
                ])->json()['results'] ?? [];
            } catch (\Exception $e) {
                $result = [];
                // Opcional: puedes guardar el error en el log
                Log::error('Error consultando Gutendex: ' . $e->getMessage());
            }

            foreach ($result as $libro) {
                $formats = $libro['formats'] ?? [];
                $url_texto = $formats['text/plain; charset=utf-8']
                    ?? $formats['text/plain; charset=us-ascii']
                    ?? null;

                $gutenberg[] = [
                    'id' => $libro['id'],
                    'titulo_original' => $libro['title'] ?? 'Sin título',
                    'titulo_traducido' => null,
                    'autores' => array_column($libro['authors'], 'name'),
                    'idioma' => $libro['languages'][0] ?? '',
                    'url_texto' => $url_texto,
                ];
            }
        }

        // Eliminar duplicados por ID
        $gutenberg = collect($gutenberg)->unique('id')->values()->all();

        // PAGINACIÓN manual
        $page = $request->input('page', 1);
        $perPage = 10; // resultados por página

        $gutenbergCollection = collect($gutenberg);

        $paginated = new LengthAwarePaginator(
            $gutenbergCollection->forPage($page, $perPage),
            $gutenbergCollection->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        // Traducir SOLO los títulos de la página actual y cachear
        $items = $paginated->items();
        foreach ($items as &$libro) {
            if (!$libro['titulo_traducido']) {
                $libro['titulo_traducido'] = $this->traducirTituloCache($libro['titulo_original'], 'es');
            }
        }
        unset($libro); // evitar errores de referencias

        // Reemplazar items traducidos en la colección paginada
        $paginated->setCollection(collect($items));

        // Traducir término original a inglés para usar en PubMed
        $traducidoIngles = Http::timeout(20)->post('http://localhost:5001/translate', [
            'q' => $query,
            'source' => 'es',
            'target' => 'en',
            'format' => 'text',
        ])->json()['translatedText'] ?? $query;

        // Buscar en PubMed usando el término traducido
        $pubmedResponse = Http::timeout(20)->get("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi", [
            'db' => 'pubmed',
            'term' => $traducidoIngles,
            'retmode' => 'json'
        ])->json();

        $pubmedIds = $pubmedResponse['esearchresult']['idlist'] ?? [];
        $pubmedResults = [];
        if (!empty($pubmedIds)) {
            $idsString = implode(',', $pubmedIds);
            $summaryResponse = Http::timeout(20)->get("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi", [
                'db' => 'pubmed',
                'id' => $idsString,
                'retmode' => 'json'
            ])->json();
            $summaries = $summaryResponse['result'] ?? [];
            foreach ($pubmedIds as $id) {
                $titulo = isset($summaries[$id]['title']) ? $summaries[$id]['title'] : 'Sin título';
                $titulo_traducido = $this->traducirTituloCache($titulo, 'es');
                $pubmedResults[] = [
                    'id' => $id,
                    'titulo' => $titulo,
                    'titulo_traducido' => $titulo_traducido
                ];
            }
        }

        return view('search.results', [
            'query' => $query,
            'gutenberg' => $paginated,
            'pubmedResults' => $pubmedResults
        ]);
    }

    protected function traducirTituloCache($titulo, $target = 'es')
    {
        return Cache::remember('titulo_' . md5($titulo . $target), 3600, function() use ($titulo, $target) {
            try {
                $detectResp = Http::timeout(20)->post('http://localhost:5001/detect', [
                    'q' => $titulo,
                ]);
                $idiomaOriginal = $detectResp->json()[0]['language'] ?? 'en';

                if ($idiomaOriginal === $target) {
                    return $titulo;
                }

                $response = Http::timeout(20)->post('http://localhost:5001/translate', [
                    'q' => $titulo,
                    'source' => $idiomaOriginal,
                    'target' => $target,
                    'format' => 'text',
                ]);

                return $response->json()['translatedText'] ?? $titulo;
            } catch (ConnectionException $e) {
                // Si hay timeout, devuelve el título original o un mensaje
                return $titulo . ' (traducción no disponible por timeout)';
            } catch (\Exception $e) {
                return $titulo;
            }
        });
    }

    public function ver(Request $request, $fuente, $id)
    {
        $contenido = '';

        if ($fuente === 'gutenberg') {
            $url_texto = $request->query('url_texto');
            if ($url_texto) {
                $url_texto = urldecode($url_texto);
                $response = Http::timeout(20)->get($url_texto);
                if ($response->successful()) {
                    $contenido = $response->body();
                }
            }
        }

        if ($fuente === 'pubmed') {
            // Obtener el abstract y metadatos del artículo PubMed
            $fetch = Http::timeout(20)->get("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi", [
                'db' => 'pubmed',
                'id' => $id,
                'retmode' => 'xml'
            ]);

            $titulo = '';
            $abstract = '';
            $autores = [];
            $doi = '';
            $year = '';
            $institucion = '';

            if ($fetch->successful()) {
                $xml = simplexml_load_string($fetch->body());
                if ($xml) {
                    $article = $xml->xpath('//PubmedArticle')[0] ?? null;
                    if ($article) {
                        $titulo = (string)($article->MedlineCitation->Article->ArticleTitle ?? '');
                        $titulo_traducido = $this->traducirChunk($titulo, 'es');
                        $abstractNodes = $article->MedlineCitation->Article->Abstract->AbstractText ?? [];
                        foreach ($abstractNodes as $node) {
                            $abstract .= (string)$node . "\n\n";
                        }
                        // Autores
                        if (isset($article->MedlineCitation->Article->AuthorList->Author)) {
                            foreach ($article->MedlineCitation->Article->AuthorList->Author as $author) {
                                $nombre = (string)($author->ForeName ?? '');
                                $apellido = (string)($author->LastName ?? '');
                                $autores[] = trim("$nombre $apellido");
                                // Institución (solo la primera encontrada)
                                if (empty($institucion) && isset($author->AffiliationInfo->Affiliation)) {
                                    $institucion = (string)$author->AffiliationInfo->Affiliation;
                                }
                            }
                        }
                        // Año
                        $year = (string)($article->MedlineCitation->Article->Journal->JournalIssue->PubDate->Year ?? '');
                        // DOI
                        if (isset($article->PubmedData->ArticleIdList->ArticleId)) {
                            foreach ($article->PubmedData->ArticleIdList->ArticleId as $idNode) {
                                if ((string)$idNode['IdType'] === 'doi') {
                                    $doi = (string)$idNode;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $contenido = $abstract ?: 'No hay resumen disponible para este artículo.';
            return view('search.view', compact('contenido', 'titulo', 'titulo_traducido', 'autores', 'year', 'doi', 'institucion'));
        }

        return view('search.view', compact('contenido'));
    }

    protected function traducirTextoLargoConProgreso($texto, $target = 'es')
    {
        $maxChunkSize = 500; // tamaño máximo por petición (500 caracteres)
        $texto = trim($texto);

        // Si el texto es muy corto, no dividir
        if (strlen($texto) <= $maxChunkSize) {
            return $this->traducirChunk($texto, $target);
        }

        // Dividir en chunks sin cortar palabras
        $chunks = [];
        $start = 0;
        $length = strlen($texto);

        while ($start < $length) {
            $chunk = mb_substr($texto, $start, $maxChunkSize);

            // Para evitar cortar palabra a la mitad, buscamos último espacio
            $lastSpace = mb_strrpos($chunk, ' ');
            if ($lastSpace !== false && ($start + $maxChunkSize) < $length) {
                $chunk = mb_substr($texto, $start, $lastSpace);
                $start += $lastSpace;
            } else {
                $start += $maxChunkSize;
            }

            $chunks[] = $chunk;
        }

        $textoTraducido = '';
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            try {
                $chunkTraducido = $this->traducirChunk($chunk, $target);
                $textoTraducido .= $chunkTraducido . ' ';
                
                // Pequeña pausa para no sobrecargar la API
                usleep(100000); // 0.1 segundos
                
            } catch (\Exception $e) {
                $textoTraducido .= $chunk . ' '; // En caso de error devolver texto original
            }
        }

        return trim($textoTraducido);
    }

    protected function traducirChunk($chunk, $target = 'es')
    {
        try {
            $detect = Http::timeout(30)->post('http://localhost:5001/detect', ['q' => $chunk]);
            $idiomaOriginal = $detect->json()[0]['language'] ?? 'en';

            if ($idiomaOriginal === $target) {
                // Ya está en el idioma destino, no traducir
                return $chunk;
            }

            $response = Http::timeout(30)->post('http://localhost:5001/translate', [
                'q' => $chunk,
                'source' => $idiomaOriginal,
                'target' => $target,
                'format' => 'text',
            ]);

            return $response->json()['translatedText'] ?? $chunk;
        } catch (ConnectionException $e) {
            // Si hay timeout, devuelve el chunk original
            return $chunk . ' (traducción no disponible)';
        } catch (\Exception $e) {
            return $chunk;
        }
    }

    protected function traducir($texto)
    {
        // Detectar el idioma del contenido
        $detect = Http::timeout(20)->post('http://localhost:5001/detect', [
            'q' => substr($texto, 0, 1000),
        ]);

        $idioma = $detect->json()[0]['language'] ?? 'unknown';

        // Si ya está en español, devolver tal cual
        if ($idioma === 'es') {
            return $texto;
        }

        // Traducir al español
        $response = Http::timeout(20)->post('http://localhost:5001/translate', [
            'q' => substr($texto, 0, 1000),
            'target' => 'es',
            'format' => 'text',
        ]);

        return $response->json()['translatedText'] ?? 'Translation not available';
    }

    protected function traducirTextoLargo($texto, $target = 'es')
    {
        $maxChunkSize = 1000; // tamaño máximo por petición
        $texto = trim($texto);

        // Dividir en chunks sin cortar palabras
        $chunks = [];
        $start = 0;
        $length = strlen($texto);

        while ($start < $length) {
            $chunk = mb_substr($texto, $start, $maxChunkSize);

            // Para evitar cortar palabra a la mitad, buscamos último espacio
            $lastSpace = mb_strrpos($chunk, ' ');
            if ($lastSpace !== false && ($start + $maxChunkSize) < $length) {
                $chunk = mb_substr($texto, $start, $lastSpace);
                $start += $lastSpace;
            } else {
                $start += $maxChunkSize;
            }

            $chunks[] = $chunk;
        }

        $textoTraducido = '';

        foreach ($chunks as $chunk) {
            try {
                $detect = Http::timeout(20)->post('http://localhost:5001/detect', ['q' => $chunk]);
                $idiomaOriginal = $detect->json()[0]['language'] ?? 'en';

                if ($idiomaOriginal === $target) {
                    // Ya está en el idioma destino, no traducir
                    $textoTraducido .= $chunk . ' ';
                    continue;
                }

                $response = Http::timeout(20)->post('http://localhost:5001/translate', [
                    'q' => $chunk,
                    'source' => $idiomaOriginal,
                    'target' => $target,
                    'format' => 'text',
                ]);

                $textoTraducido .= ($response->json()['translatedText'] ?? $chunk) . ' ';
            } catch (\Exception $e) {
                $textoTraducido .= $chunk . ' '; // En caso de error devolver texto original
            }
        }

        return trim($textoTraducido);
    }

    public function traducirAjax(Request $request)
    {
        $texto = $request->input('texto');
        $inicio = $request->input('inicio', 0);
        $fin = $request->input('fin', 500);
        
        if (empty($texto)) {
            return response()->json(['error' => 'No se proporcionó texto para traducir']);
        }

        // Extraer el fragmento de texto a traducir
        $fragmento = mb_substr($texto, $inicio, $fin - $inicio);
        
        if (empty($fragmento)) {
            return response()->json(['error' => 'Fragmento vacío']);
        }

        try {
            $traducido = $this->traducirChunk($fragmento, 'es');
            
            return response()->json([
                'traducido' => $traducido,
                'inicio' => $inicio,
                'fin' => $fin,
                'progreso' => min(100, ($fin / strlen($texto)) * 100)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al traducir: ' . $e->getMessage()]);
        }
    }
}
