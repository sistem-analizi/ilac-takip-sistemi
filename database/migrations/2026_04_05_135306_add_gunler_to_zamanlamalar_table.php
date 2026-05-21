<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('zamanlamalar', function (Blueprint $table) {
            // alinacak_saat sütunundan hemen sonra günleri tutacağımız JSON sütununu ekliyoruz
            $table->json('gunler')->nullable()->after('alinacak_saat');
        });
    }

    public function down()
    {
        Schema::table('zamanlamalar', function (Blueprint $table) {
            $table->dropColumn('gunler');
        });
    }
};
