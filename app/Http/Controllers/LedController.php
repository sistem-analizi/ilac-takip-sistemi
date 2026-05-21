<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedController extends Controller
{
    public function getDurum($bolme_id)
    {
        $z = DB::table('zamanlamalar')->where('bolme_id', $bolme_id)->first();
        return response()->json([
            'kirmizi' => ($z && $z->aktif_mi) ? 1 : 0,
            'yesil'   => ($z && $z->aktif_mi) ? 0 : 1
        ]);
    }

    public function handleButon($id)
    {
        $zamanlama = DB::table('zamanlamalar')
            ->join('ilaclar', 'zamanlamalar.ilac_id', '=', 'ilaclar.id')
            ->where('zamanlamalar.bolme_id', $id)
            ->select('zamanlamalar.*', 'ilaclar.ilac_adi')
            ->first();

        if ($zamanlama && $zamanlama->aktif_mi == 1) {

            DB::table('zamanlamalar')
                ->where('bolme_id', $id)
                ->update(['aktif_mi' => 0]);

            DB::table('sistem_kayitlari')->insert([
                'cihaz_id'     => 1,
                'bolme_id'     => $id,
                'durum'        => $zamanlama->ilac_adi . ' alındı',
                'islem_zamani' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'İlaç alındı, sistem kaydı oluşturuldu.'
            ]);
        }

        return response()->json([
            'status' => 'ignored',
            'message' => 'LED zaten yeşil, işlem yapılmadı.'
        ]);
    }

    public function getTumDurumlar()
    {
        $zamanlamalar = DB::table('zamanlamalar')->get();
        $durumlar = [];

        foreach ($zamanlamalar as $z) {
            $durumlar['bolme_' . $z->bolme_id] = $z->aktif_mi ? 1 : 0;
        }

        return response()->json($durumlar);
    }
}
