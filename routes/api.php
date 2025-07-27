<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//API route for register new user
Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register']);
//API route for login user
Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);

Route::prefix('wilayah')
    ->middleware('siappakai')
    ->group(function () {
        Route::get('cari-desa', [App\Http\Controllers\API\WilayahController::class, 'cariDesa']);
    });

//Protecting Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/profile', function (Request $request) {
        return auth()->user();
    });

    //API route for update user
    Route::post('/user/update/{user}', [App\Http\Controllers\API\AuthController::class, 'update']);

    // API route for logout user
    Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout']);

    Route::post('/newsite', [App\Http\Controllers\API\SiteBuilderController::class, 'store']);
    Route::post('/pembaruan-token/{pelanggan}', [App\Http\Controllers\API\TokenUpdateController::class, 'pembaruan_token']);
    Route::post('/update-token', [App\Http\Controllers\API\TokenUpdateController::class, 'update']);
    Route::post('/perpanjang-saas/{pelanggan}', [App\Http\Controllers\API\SiteUpdateController::class, 'perpanjang_saas']);
    Route::post('/perpanjang-premium/{pelanggan}', [App\Http\Controllers\API\SiteUpdateController::class, 'perpanjang_premium']);
    Route::post('/ubah-domain/{pelanggan}', [App\Http\Controllers\API\SiteUpdateController::class, 'ubah_domain']);
    Route::post('/hapus-pelanggan/{pelanggan}', [App\Http\Controllers\API\SiteDeleteController::class, 'hapus_pelanggan']);
    Route::post('/memasang-tema-pro', [App\Http\Controllers\API\TemaProController::class, 'store']);
    Route::get('/wilayah', [App\Http\Controllers\API\WilayahController::class, 'listWilayah']);
});
