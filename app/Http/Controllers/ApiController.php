<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bolme;
use App\Models\Zamanlama;
use App\Models\SistemKaydi;

class ApiController extends Controller
{
    public function ledDurum()
    {
        $durumlar = [];
        $bolmeler = Bolme::with('zamanlamalar')->get();

        foreach ($bolmeler as $bolme) {
            $aktif_mi = $bolme->zamanlamalar->where('aktif_mi', 1)->isNotEmpty() ? 1 : 0;
            $durumlar["bolme_" . $bolme->bolme_no] = $aktif_mi;
        }

        return response()->json($durumlar);
    }

    public function butonBasildi($bolme_no)
    {
        $bolme = Bolme::where('bolme_no', $bolme_no)->first();
        if ($bolme) {
            $zamanlama = Zamanlama::with('ilac')->where('bolme_id', $bolme->id)->first();

            if ($zamanlama && $zamanlama->ilac) {
                Zamanlama::where('bolme_id', $bolme->id)->update(['aktif_mi' => 0]);

                SistemKaydi::create([
                    'cihaz_id' => $bolme->cihaz_id,
                    'bolme_id' => $bolme->id,
                    'ilac_adi' => $zamanlama->ilac->ilac_adi,
                    'dozaj' => $zamanlama->ilac->dozaj,
                    'planlanan_saat' => $zamanlama->alinacak_saat,
                    'durum' => 'Sanal butondan onaylandı'
                ]);
                return response()->json(['status' => 'success']);
            }
        }
        return response()->json(['status' => 'error'], 404);
    }
}
