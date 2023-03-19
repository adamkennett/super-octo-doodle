<?php

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MovieController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/tinkering', function (Request $request) {
    $thmdapi = new \App\External\TheMovieDatabaseApiService(new Client());
    $result = $thmdapi->getMovieRating('Fight Club');

    $omdb = new \App\External\OpenMovieApiService(new Client());
    $result2 = $omdb->getMovieRating('Fight Club');

    dd($result . '<- TMDB : OMDB -> ' . $result2);
});

Route::apiResources([
    'movies' => MovieController::class
]);
