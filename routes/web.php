<?php


use Illuminate\Support\Facades\Route;
use App\Models\Loan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\PublicSearchController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');

});

Route::get('/admin-redirect', function () {
    return redirect('/admin');

});

// Rutas públicas para consulta de libros
Route::get('/search', [PublicSearchController::class, 'buscar']);
Route::get('/search/results', [PublicSearchController::class, 'resultados']);
Route::get('/public-search/ver/{fuente}/{id}', [PublicSearchController::class, 'ver'])->name('public-search.ver');
Route::post('/public-search/traducir', [PublicSearchController::class, 'traducirAjax'])->name('public-search.traducir');
