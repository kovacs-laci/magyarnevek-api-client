<?php

use App\Http\Controllers\NameController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/phpinfo', function () {
    phpinfo();
});

Route::get('/test-http', function () {
    try {
        $response = Http::timeout(30)
            ->get('https://www.google.com');

        return [
            'status' => $response->status(),
        ];
    } catch (Throwable $e) {
        return $e->getMessage();
    }
});

Route::get('/test-names', function () {

    $response = Http::timeout(30)
        ->withHeaders([
            'x-client-id' => env('API_CLIENT_ID'),
            'x-api-key' => env('API_KEY'),
        ])
        ->get('https://magyarnevek.hu/api/v1/names');

    return [
        'status' => $response->status(),
        'body' => $response->json(),
    ];
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/names', [NameController::class, 'index'])->name('names.index');
Route::get('/names/{name}', [NameController::class, 'show'])->name('names.show');
Route::get('/namedays/{name}', [NameController::class, 'namedays'])->name('names.namedays');
Route::get('/nameforms/{name}', [NameController::class, 'nameforms'])->name('names.nameforms');

Route::get('/search', [SearchController::class, 'index'])->name('search.index');
