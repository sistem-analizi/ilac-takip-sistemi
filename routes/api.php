<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::controller(ApiController::class)->group(function () {
    Route::get('/led-durum-hepsi', 'ledDurum');
    Route::post('/buton-basildi/{bolme_no}', 'butonBasildi');
});
