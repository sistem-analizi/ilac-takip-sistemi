<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IlacController;

// ==========================================
// DASHBOARD & GENEL YÖNETİM ROTALARI
// ==========================================
Route::controller(DashboardController::class)->group(function () {
    Route::get('/', 'index')->name('dashboard');
    Route::get('/sistem-kayitlari', 'tumKayitlar')->name('log.index');
    Route::get('/ilac-gecmisi', 'ilacGecmisi')->name('gecmis.index');

    // Ayarlar Grubu
    Route::prefix('ayarlar')->name('ayarlar.')->group(function () {
        Route::get('/', 'ayarlar')->name('index');
        Route::post('/ilac-ekle', 'ilacKatalogEkle')->name('ilacEkle');
        Route::post('/ilac-sil/{id}', 'ilacKatalogSil')->name('ilacSil');
        Route::post('/ilac-guncelle/{id}', 'ilacKatalogGuncelle')->name('ilacGuncelle');
    });

    // Profil Grubu
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/', 'profil')->name('index');
        Route::post('/guncelle', 'profilGuncelle')->name('guncelle');
    });
});

// ==========================================
// İLAÇ & BÖLME YÖNETİM ROTALARI
// ==========================================
Route::controller(IlacController::class)->group(function () {
    Route::get('/programim', 'index')->name('ilac.index');
    Route::get('/ilac-ekle', 'create')->name('ilac.create');
    Route::post('/ilac-kaydet', 'store')->name('ilac.store');
    Route::get('/ilac-duzenle/{id}', 'edit')->name('ilac.edit');
    Route::put('/ilac-guncelle/{id}', 'update')->name('ilac.update');
    Route::post('/bolme-temizle/{bolme_id}', 'temizle')->name('bolme.temizle');
});

Route::get('/veritabani-coz', function() {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'BolmeSeeder']);
    return "Bölmeler başarıyla veritabanına eklendi!";
});
