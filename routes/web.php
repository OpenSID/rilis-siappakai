<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\{LoginController, LogoutController};
use App\Http\Controllers\Helpers\KoneksiController;
use App\Http\Controllers\OpendkController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\Pengaturan\AplikasiController;
use App\Http\Controllers\Pengaturan\JadwalTugasController;
use App\Http\Controllers\Pengaturan\PengaturanTemaController;
use App\Http\Controllers\Pengaturan\PenggunaController;
use App\Http\Controllers\Pengaturan\SslWildcardController;
use App\Http\Controllers\PengaturanModulController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('logout', [LogoutController::class, 'index'])->name('logout');
Route::get('koneksi', [KoneksiController::class, 'authKoneksi'])->name('koneksi');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dasbor');

    // Data Pelanggan
    Route::post('pelanggan/update-domain', [PelangganController::class, 'updateDomain'])->name('pelanggan.updateDomain');
    Route::resource("pelanggan", PelangganController::class)->except(["show"]);
    Route::get('pelanggan/{remain?}', [PelangganController::class, 'index'])->name('pelanggan.remain');
    Route::post('pelanggan/konfigurasi-ftp', [PelangganController::class, 'configFtp'])->name('pelanggan.configFtp');
    Route::post('pelanggan/aktifkan-ssl', [PelangganController::class, 'aktifSsl'])->name('pelanggan.aktifSsl');
    Route::post('pelanggan/perbarui-token-masal', [PelangganController::class, 'updateTokenMasal'])->name('pelanggan.updateTokenMasal');
    Route::post('pelanggan/unduh-dbgabungan', [PelangganController::class, 'unduhDatabaseGabungan'])->name('pelanggan.unduhDatabaseGabungan');

    // Data OpenDK
    Route::resource("opendk", OpendkController::class)->except(["show"]);
    Route::post('opendk/{remain?}', [OpendkController::class, 'index'])->name('opendk.remain');
    Route::post('opendk/aktifkan-ssl', [OpendkController::class, 'aktifSsl'])->name('opendk.aktifSsl');
    Route::post('opendk/update-domain', [OpendkController::class, 'updateDomain'])->name('opendk.updateDomain');

    // queue monitoring
    Route::group(['prefix' => 'jobs'], function () {
        Route::queueMonitor();
    });

    // pengaturan
    if (env('OPENKAB') == 'true') {
        Route::resource("pengguna", PenggunaController::class)->except(["show"]);
        Route::delete('/hapus-data-pengguna', [PenggunaController::class, 'deleteChecked'])->name('pengguna.deleteSelected');
    }

    Route::resource("aplikasi", AplikasiController::class)->except(["show"]);
    Route::put("aplikasi/update-image/{id}", [AplikasiController::class, 'updateImage'])->name('aplikasi.updateImage');

    Route::resource("ssl-wildcard", SslWildcardController::class);
    Route::delete('/hapus-ssl-wildcard', [SslWildcardController::class, 'deleteChecked'])->name('ssl-wildcard.deleteSelected');
    Route::get('/ssl/download/{id}/{type}', [SslWildcardController::class, 'download'])->name('ssl.download');
    Route::resource("jadwal-tugas", JadwalTugasController::class)->except(["show"]);
    Route::delete('/hapus-jadwal-tugas', [JadwalTugasController::class, 'deleteChecked'])->name('jadwal-tugas.deleteSelected');
    Route::resource("tema", PengaturanTemaController::class)->except(["show"]);
    Route::delete('/hapus-pengaturan-tema', [PengaturanTemaController::class, 'deleteChecked'])->name('tema.deleteSelected');
    Route::resource("modul", PengaturanModulController::class)->except(["edit", "update"]);
});

/** Guest */
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']); // valid jika ingin panggil route di formya
});

// Debug route for testing Sentry integration (only in debug mode)
if (config('app.debug')) {
    Route::get('/test-sentry', function () {
        // Test Sentry error capture
        throw new \Exception('Test Sentry error - this is intentional for testing Sentry integration');
    })->name('test-sentry');
}
