<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\{LoginController, LogoutController};
use App\Http\Controllers\Helpers\KoneksiController;
use App\Http\Controllers\Monitoring\ApiHealthCheckController;
use App\Http\Controllers\OpendkController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\Pengaturan\AplikasiController;
use App\Http\Controllers\Pengaturan\DomainSyncController;
use App\Http\Controllers\Pengaturan\GlobalSettingsController;
use App\Http\Controllers\Pengaturan\JadwalTugasController;
use App\Http\Controllers\Pengaturan\MasterCloudflareController;
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
    Route::post('pelanggan/backup/{id}', [PelangganController::class, 'backup'])->name('pelanggan.backup');
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

    Route::get("aplikasi/status-sinkron-wilayah", [AplikasiController::class, 'statusSinkronWilayah'])->name('aplikasi.statusSinkronWilayah');
    Route::resource("aplikasi", AplikasiController::class)->except(["show"]);
    Route::put("aplikasi/update-image/{id}", [AplikasiController::class, 'updateImage'])->name('aplikasi.updateImage');

    Route::resource("ssl-wildcard", SslWildcardController::class);
    Route::delete('/hapus-ssl-wildcard', [SslWildcardController::class, 'deleteChecked'])->name('ssl-wildcard.deleteSelected');
    Route::get('/ssl/download/{id}/{type}', [SslWildcardController::class, 'download'])->name('ssl.download');
    Route::resource("jadwal-tugas", JadwalTugasController::class)->except(["show"]);
    Route::delete('/hapus-jadwal-tugas', [JadwalTugasController::class, 'deleteChecked'])->name('jadwal-tugas.deleteSelected');
    Route::resource("master-cloudflare", MasterCloudflareController::class)->except(["show"]);
    Route::delete('/hapus-master-cloudflare', [MasterCloudflareController::class, 'deleteChecked'])->name('master-cloudflare.deleteSelected');

    // Cloudflare Rule Master
    Route::resource("cloudflare-rule-master", \App\Http\Controllers\Pengaturan\CloudflareRuleMasterController::class)->except(["show"]);
    Route::delete('/hapus-cloudflare-rule-master', [\App\Http\Controllers\Pengaturan\CloudflareRuleMasterController::class, 'deleteChecked'])->name('cloudflare-rule-master.deleteSelected');
    Route::get('/cloudflare-rule-master/history', [\App\Http\Controllers\Pengaturan\CloudflareRuleMasterController::class, 'history'])->name('cloudflare-rule-master.history');
    Route::get('/cloudflare-rule-master/history/{batchId}', [\App\Http\Controllers\Pengaturan\CloudflareRuleMasterController::class, 'showHistory'])->name('cloudflare-rule-master.history.show');
    Route::post('/cloudflare-rule-master/retry/{batchId}', [\App\Http\Controllers\Pengaturan\CloudflareRuleMasterController::class, 'retry'])->name('cloudflare-rule-master.retry');
    Route::get('/cloudflare-rule-master/status/{batchId}', [\App\Http\Controllers\Pengaturan\CloudflareRuleMasterController::class, 'status'])->name('cloudflare-rule-master.status');
    Route::post('/cloudflare-rule-master/deploy', [\App\Http\Controllers\Pengaturan\CloudflareRuleMasterController::class, 'deploy'])->name('cloudflare-rule-master.deploy');


    // Domain Sync
    Route::get("domain-sync", [DomainSyncController::class, 'index'])->name('domain-sync.index');
    Route::post("domain-sync/sync-all", [DomainSyncController::class, 'syncAll'])->name('domain-sync.syncAll');
    Route::post("domain-sync/sync-one", [DomainSyncController::class, 'syncOne'])->name('domain-sync.syncOne');
    Route::get("domain-sync/statistics", [DomainSyncController::class, 'statistics'])->name('domain-sync.statistics');

    Route::resource("tema", PengaturanTemaController::class)->except(["show"]);
    Route::delete('/hapus-pengaturan-tema', [PengaturanTemaController::class, 'deleteChecked'])->name('tema.deleteSelected');
    Route::resource("modul", PengaturanModulController::class)->except(["edit", "update"]);

    // Pengaturan Global (global.json per pelanggan)
    Route::get('pengaturan-global', [GlobalSettingsController::class, 'index'])->name('pengaturan-global.index');
    Route::get('pengaturan-global/{pelanggan}/{key}/edit', [GlobalSettingsController::class, 'edit'])->name('pengaturan-global.edit');
    Route::put('pengaturan-global/{pelanggan}/{key}', [GlobalSettingsController::class, 'update'])->name('pengaturan-global.update');
    Route::post('pengaturan-global/generate', [GlobalSettingsController::class, 'generate'])->name('pengaturan-global.generate');
    Route::post('pengaturan-global/bulk-update', [GlobalSettingsController::class, 'bulkUpdate'])->name('pengaturan-global.bulk-update');
    Route::post('pengaturan-global/deploy-all', [GlobalSettingsController::class, 'deployAll'])->name('pengaturan-global.deploy-all');
    Route::post('pengaturan-global/update-template', [GlobalSettingsController::class, 'updateTemplate'])->name('pengaturan-global.update-template');
    Route::get('pengaturan-global/{pelanggan}/export', [GlobalSettingsController::class, 'export'])->name('pengaturan-global.export');

    // monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/api-health-check', [ApiHealthCheckController::class, 'index'])
            ->name('api-health-check.index');

        Route::get('/api-health-check/pelanggan-ids', [ApiHealthCheckController::class, 'getPelangganIds'])
            ->name('api-health-check.pelanggan-ids');

        Route::get('/api-health-check/summary', [ApiHealthCheckController::class, 'summary'])
            ->name('api-health-check.summary');

        Route::post('/api-health-check/{pelangganId}/check', [ApiHealthCheckController::class, 'checkSingle'])
            ->name('api-health-check.check-single');
    });
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
