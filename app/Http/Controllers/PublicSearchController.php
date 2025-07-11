<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Promise;
use GuzzleHttp\Client;

class PublicSearchController extends Controller
{
    public function buscar()
    {
        return view('search.search');
    }

    public function resultados(Request $request)
    {
        $query = $request->input('q');
        $lang = $request->input('lang', 'es');

        $idiomas = ['en', 'de', 'fr'];
        $traducciones = [];
        $client = new Client();
        $promises = [];
        foreach ($idiomas as $idioma) {
            $cacheKey = $this->getTraduccionCacheKey($query, $idioma);
            $traduccionCache = Cache::get($cacheKey);
            if ($traduccionCache !== null) {
                $traducciones[$idioma] = $traduccionCache;
            } else {
                $idiomaDetectado = Cache::remember('detect_' . md5($query), 86400, function() use ($query) {
                    return $this->safeApiCall(function() use ($query) {
                        $resp = Http::timeout(10)->post('http://localhost:5001/detect', ['q' => $query]);
                        return $resp->json()[0]['language'] ?? 'es';
                    }, 'es');
                });
                if ($idiomaDetectado === $idioma) {
                    $traducciones[$idioma] = $query;
                    Cache::put($cacheKey, $query, 86400);
                } else {
                    $promises[$idioma] = $client->postAsync('http://localhost:5001/translate', [
                        'form_params' => [
                            'q' => $query,
                            'source' => $idiomaDetectado,
                            'target' => $idioma,
                            'format' => 'text',
                        ],
                        'timeout' => 20,
                    ]);
                }
            }
        }
        if (!empty($promises)) {
            $responses = Promise\Utils::settle($promises)->wait();
            foreach ($responses as $idioma => $response) {
                if ($response['state'] === 'fulfilled') {
                    $body = json_decode($response['value']->getBody(), true);
                    $traducciones[$idioma] = $body['translatedText'] ?? null;
                    Cache::put($this->getTraduccionCacheKey($query, $idioma), $traducciones[$idioma], 86400);
                } else {
                    $traducciones[$idioma] = null;
                }
            }
        }

        // Log de depuración: mostrar términos originales y traducidos
        \Log::info('Términos de búsqueda para Gutendex', [
            'original' => $query,
            'traducciones' => $traducciones
        ]);

        // Construir array de términos para buscar en Gutendex (original + traducciones)
        $terminos = array_filter(array_merge([$query], $traducciones));
        $terminoBusqueda = implode(' OR ', $terminos);

        // Log de depuración: mostrar consulta final enviada a Gutendex
        \Log::info('Consulta enviada a Gutendex', [
            'url' => 'https://gutendex.com/books',
            'params' => ['search' => $terminoBusqueda]
        ]);





        // --- Búsquedas asíncronas en APIs externas ---
        $promises = [];
        $client = new Client();
        
        // Inicializar variables por defecto
        $gutenbergResults = [];
        $pmcResults = [];
        $pubmedResults = [];
        
        // Inicializar $gutenberg con colección vacía
        $gutenberg = collect([]);
        
        // Búsqueda en Gutenberg (asíncrona)
        $gutenbergCacheKey = $this->getGutenbergCacheKey($terminoBusqueda);
        $gutenbergResults = Cache::remember($gutenbergCacheKey, 86400, function() use ($terminoBusqueda, $client, &$promises) {
            $promises['gutenberg'] = $client->getAsync("https://gutendex.com/books", [
                'query' => [
                    'search' => $terminoBusqueda,
                    'page' => 1,
                ],
                'timeout' => 40,
            ]);
            return []; // Retornamos array vacío, se procesará después
        });
        
        // Búsqueda en PMC (asíncrona)
        $pmcCacheKey = 'pmc_' . md5($query);
        $pmcResults = Cache::remember($pmcCacheKey, 86400, function() use ($query, $client, &$promises) {
            $promises['pmc_search'] = $client->getAsync('https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', [
                'query' => [
                    'db' => 'pmc',
                    'term' => $query,
                    'retmax' => 20,
                    'retmode' => 'json'
                ],
                'timeout' => 20,
            ]);
            return []; // Retornamos array vacío, se procesará después
        });
        
        // Búsqueda en PubMed (asíncrona)
        $pubmedCacheKey = $this->getPubmedCacheKey($query);
        $pubmedResponse = Cache::remember($pubmedCacheKey, 86400, function() use ($query, $client, &$promises) {
            // Primero traducir al inglés
            $traducidoIngles = Http::timeout(20)->post('http://localhost:5001/translate', [
                'q' => $query,
                'source' => 'es',
                'target' => 'en',
                'format' => 'text',
            ])->json()['translatedText'] ?? $query;
            
            $promises['pubmed_search'] = $client->getAsync("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi", [
                'query' => [
                    'db' => 'pubmed',
                    'term' => '"' . $traducidoIngles . '"',
                    'retmax' => 20,
                    'retmode' => 'json'
                ],
                'timeout' => 20,
            ]);
            return []; // Retornamos array vacío, se procesará después
        });
        
        // Ejecutar todas las promesas en paralelo
        if (!empty($promises)) {
            $responses = Promise\Utils::settle($promises)->wait();
            
            // Procesar respuesta de Gutenberg
            if (isset($responses['gutenberg']) && $responses['gutenberg']['state'] === 'fulfilled') {
                $gutenbergData = json_decode($responses['gutenberg']['value']->getBody(), true);
                $gutenbergResults = $gutenbergData['results'] ?? [];
                Cache::put($gutenbergCacheKey, $gutenbergResults, 86400);
            }
            
            // Procesar y formatear resultados de Gutenberg
            $gutenberg = collect($gutenbergResults)
                ->unique('id')
                ->take(20)
                ->values()
                ->map(function($libro) {
                    $formats = $libro['formats'] ?? [];
                    $url_texto = $formats['text/plain; charset=utf-8']
                        ?? $formats['text/plain; charset=us-ascii']
                        ?? null;
                    return [
                        'id' => $libro['id'],
                        'titulo_original' => $libro['title'] ?? 'Sin título',
                        'titulo_traducido' => null,
                        'autores' => array_column($libro['authors'], 'name'),
                        'idioma' => $libro['languages'][0] ?? '',
                        'url_texto' => $url_texto,
                    ];
                });
            
            // Procesar respuesta de PMC
            if (isset($responses['pmc_search']) && $responses['pmc_search']['state'] === 'fulfilled') {
                $pmcSearchData = json_decode($responses['pmc_search']['value']->getBody(), true);
                $pmcIds = $pmcSearchData['esearchresult']['idlist'] ?? [];
                
                if (!empty($pmcIds)) {
                    // Pequeña pausa para respetar límites de NCBI
                    usleep(100000); // 0.1 segundos
                    
                    $idsString = implode(',', $pmcIds);
                    $summary = Http::timeout(20)->get('https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi', [
                        'db' => 'pmc',
                        'id' => $idsString,
                        'retmode' => 'json'
                    ])->json();
                    
                    $summaries = $summary['result'] ?? [];
                    $pmcResults = [];
                    foreach ($pmcIds as $id) {
                        $titulo = $summaries[$id]['title'] ?? 'Sin título';
                        $titulo_traducido = Cache::remember('titulo_pmc_' . md5($titulo . 'es'), 86400, function() use ($titulo) {
                            return $this->safeApiCall(function() use ($titulo) {
                                return $this->traducirTituloCache($titulo, 'es');
                            }, $titulo);
                        });
                        $pmcResults[] = [
                            'id' => $id,
                            'titulo' => $titulo,
                            'titulo_traducido' => $titulo_traducido,
                        ];
                    }
                    Cache::put($pmcCacheKey, $pmcResults, 86400);
                }
            }
            
            // Procesar respuesta de PubMed
            if (isset($responses['pubmed_search']) && $responses['pubmed_search']['state'] === 'fulfilled') {
                $pubmedSearchData = json_decode($responses['pubmed_search']['value']->getBody(), true);
                $pubmedIds = $pubmedSearchData['esearchresult']['idlist'] ?? [];
                
                if (!empty($pubmedIds)) {
                    // Pequeña pausa para respetar límites de NCBI
                    usleep(100000); // 0.1 segundos
                    
                    $idsString = implode(',', $pubmedIds);
                    $summaryResponse = $this->safeApiCall(function() use ($idsString) {
                        return Http::timeout(20)->get("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi", [
                            'db' => 'pubmed',
                            'id' => $idsString,
                            'retmode' => 'json'
                        ])->json();
                    }, []);
                    
                    $summaries = $summaryResponse['result'] ?? [];
                    $pubmedResults = [];
                    foreach ($pubmedIds as $id) {
                        $titulo = isset($summaries[$id]['title']) ? $summaries[$id]['title'] : 'Sin título';
                        $titulo_traducido = Cache::remember('titulo_' . md5($titulo . 'es'), 86400, function() use ($titulo) {
                            return $this->safeApiCall(function() use ($titulo) {
                                return $this->traducirTituloCache($titulo, 'es');
                            }, $titulo);
                        });
                        $pubmedResults[] = [
                            'id' => $id,
                            'titulo' => $titulo,
                            'titulo_traducido' => $titulo_traducido
                        ];
                    }
                    Cache::put($pubmedCacheKey, $pubmedResults, 86400);
                }
            }
        }
        
        // PAGINACIÓN manual para Gutenberg
        $page = $request->input('page', 1);
        $perPage = 10;
        
        // Crear query parameters específicos para Gutenberg
        $gutenbergQueryParams = $request->query();
        unset($gutenbergQueryParams['pmc_page']); // Remover la página de PMC
        unset($gutenbergQueryParams['pubmed_page']); // Remover la página de PubMed
        
        $paginated = new LengthAwarePaginator(
            $gutenberg->forPage($page, $perPage),
            $gutenberg->count(),
            $perPage,
            $page,
            [
                'path' => url()->current(), 
                'query' => $gutenbergQueryParams,
                'pageName' => 'page'
            ]
        );

        // Traducir SOLO los títulos de la página actual y cachear
        $items = $paginated->items();
        foreach ($items as &$libro) {
            if (!$libro['titulo_traducido']) {
                $libro['titulo_traducido'] = Cache::remember('titulo_' . md5($libro['titulo_original'] . 'es'), 86400, function() use ($libro) {
                    return $this->safeApiCall(function() use ($libro) {
                        return $this->traducirTituloCache($libro['titulo_original'], 'es');
                    }, $libro['titulo_original']);
                });
            }
        }
        unset($libro);
        $paginated->setCollection(collect($items));

        // Paginación para PMC
        $pmcCollection = collect($pmcResults);
        $pmcPage = $request->input('pmc_page', 1);
        $pmcPerPage = 10;
        
        // Crear query parameters específicos para PMC
        $pmcQueryParams = $request->query();
        unset($pmcQueryParams['page']); // Remover la página principal
        unset($pmcQueryParams['pubmed_page']); // Remover la página de PubMed
        
        $pmcPaginated = new LengthAwarePaginator(
            $pmcCollection->forPage($pmcPage, $pmcPerPage),
            $pmcCollection->count(),
            $pmcPerPage,
            $pmcPage,
            [
                'path' => url()->current(), 
                'query' => $pmcQueryParams,
                'pageName' => 'pmc_page'
            ]
        );



        // Paginación para PubMed
        $pubmedCollection = collect($pubmedResults);
        $pubmedPage = $request->input('pubmed_page', 1);
        $pubmedPerPage = 10;
        
        // Crear query parameters específicos para PubMed
        $pubmedQueryParams = $request->query();
        unset($pubmedQueryParams['page']); // Remover la página principal
        unset($pubmedQueryParams['pmc_page']); // Remover la página de PMC
        
        $pubmedPaginated = new LengthAwarePaginator(
            $pubmedCollection->forPage($pubmedPage, $pubmedPerPage),
            $pubmedCollection->count(),
            $pubmedPerPage,
            $pubmedPage,
            [
                'path' => url()->current(), 
                'query' => $pubmedQueryParams,
                'pageName' => 'pubmed_page'
            ]
        );

        return view('search.results', [
            'query' => $query,
            'gutenberg' => $paginated,
            'pubmedResults' => $pubmedPaginated,
            'pmcResults' => $pmcPaginated,
        ]);
    }

    // Helpers para claves de caché
    protected function getTraduccionCacheKey($query, $idioma)
    {
        return 'traduccion_' . md5($query . '_' . $idioma);
    }
    protected function getGutenbergCacheKey($termino)
    {
        return 'gutenberg_' . md5($termino);
    }
    protected function getPubmedCacheKey($query)
    {
        return 'pubmed_' . md5($query);
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
            // Obtener metadatos del libro desde Gutendex
            $bookResponse = Http::timeout(20)->get("https://gutendex.com/books/{$id}");
            $bookData = $bookResponse->json();
            
            $titulo = $bookData['title'] ?? '';
            $titulo_traducido = $this->traducirChunk($titulo, 'es');
            $autores = array_column($bookData['authors'] ?? [], 'name');
            $year = $bookData['authors'][0]['birth_year'] ?? ''; // Año aproximado del autor
            $temas = $bookData['subjects'] ?? [];
            $idioma = $bookData['languages'][0] ?? '';
            $descargas = $bookData['download_count'] ?? 0;
            
            // Obtener el contenido del texto
            $url_texto = $request->query('url_texto');
            if ($url_texto) {
                $url_texto = urldecode($url_texto);
                $response = Http::timeout(20)->get($url_texto);
                if ($response->successful()) {
                    $contenido = $response->body();
                }
            }
            
            return view('search.view', compact('contenido', 'titulo', 'titulo_traducido', 'autores', 'year', 'temas', 'idioma', 'descargas'));
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

        if ($fuente === 'pmc') {
            // Obtener el contenido completo del artículo PMC
            $fetch = Http::timeout(30)->get("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi", [
                'db' => 'pmc',
                'id' => $id,
                'retmode' => 'xml'
            ]);

            $titulo = '';
            $titulo_traducido = '';
            $autores = [];
            $year = '';
            $doi = '';
            $abstract = '';
            $contenido_completo = '';

            if ($fetch->successful()) {
                $xml = simplexml_load_string($fetch->body());
                if ($xml) {
                    // Extraer título
                    $titulo = (string)($xml->article->front->{'article-meta'}->{'title-group'}->{'article-title'} ?? '');
                    $titulo_traducido = $this->traducirChunk($titulo, 'es');
                    
                    // Extraer autores
                    $contribGroup = $xml->article->front->{'article-meta'}->{'contrib-group'} ?? null;
                    if ($contribGroup && isset($contribGroup->contrib)) {
                        foreach ($contribGroup->contrib as $contrib) {
                            if ((string)$contrib['contrib-type'] === 'author') {
                                $nombre = (string)($contrib->name->{'given-names'} ?? '');
                                $apellido = (string)($contrib->name->surname ?? '');
                                $autores[] = trim("$nombre $apellido");
                            }
                        }
                    }
                    
                    // Extraer año
                    $year = (string)($xml->article->front->{'article-meta'}->{'pub-date'}->year ?? '');
                    
                    // Extraer DOI
                    $doi = '';
                    if (isset($xml->article->front->{'article-meta'}->{'article-id'})) {
                        foreach ($xml->article->front->{'article-meta'}->{'article-id'} as $articleId) {
                            $idType = (string)($articleId['pub-id-type'] ?? '');
                            if ($idType === 'doi') {
                                $doi = (string)$articleId;
                                break;
                            }
                        }
                    }
                    
                    // Extraer abstract
                    $abstractNode = $xml->article->front->{'article-meta'}->abstract ?? null;
                    if ($abstractNode && isset($abstractNode->p)) {
                        foreach ($abstractNode->p as $p) {
                            $abstract .= (string)$p . "\n\n";
                        }
                    }
                    
                    // Extraer contenido completo del artículo
                    $bodyNode = $xml->article->body ?? null;
                    if ($bodyNode && isset($bodyNode->sec)) {
                        foreach ($bodyNode->sec as $sec) {
                            // Título de la sección
                            if (isset($sec->title)) {
                                $contenido_completo .= "\n\n" . (string)$sec->title . "\n\n";
                            }
                            
                            // Párrafos de la sección
                            if (isset($sec->p)) {
                                foreach ($sec->p as $p) {
                                    $contenido_completo .= (string)$p . "\n\n";
                                }
                            }
                        }
                    }
                }
            }
            
            // Combinar abstract y contenido completo
            $contenido = $abstract . "\n\n" . $contenido_completo;
            if (empty($contenido)) {
                $contenido = 'No se pudo obtener el contenido completo del artículo.';
            }
            
            return view('search.view', compact('contenido', 'titulo', 'titulo_traducido', 'autores', 'year', 'doi'));
        }

        return view('search.view', compact('contenido'));
    }

    protected function traducirTextoLargoConProgreso($texto, $target = 'es')
    {
        $maxChunkSize = 2000; // tamaño máximo por petición (2000 caracteres)
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
        $fin = $request->input('fin', 2000);
        
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

    // Helper centralizado para llamadas seguras a APIs externas
    protected function safeApiCall(callable $callback, $default = [])
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            \Log::error('Error en consulta externa: ' . $e->getMessage());
            return $default;
        }
    }
}
