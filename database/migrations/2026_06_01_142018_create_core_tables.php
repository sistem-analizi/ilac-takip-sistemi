<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Cihazlar Tablosu
        Schema::create('cihazlar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kullanici_id')->nullable();
            $table->string('cihaz_kodu')->nullable();
            $table->string('buzzer_pin')->nullable();
            $table->timestamp('olusturulma_tarihi')->nullable();
            $table->timestamp('guncellenme_tarihi')->nullable();
        });

        // 2. İlaçlar Tablosu
        Schema::create('ilaclar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kullanici_id')->nullable();
            $table->string('ilac_adi');
            $table->string('dozaj')->nullable();
            $table->timestamp('olusturulma_tarihi')->nullable();
            $table->timestamp('guncellenme_tarihi')->nullable();
        });

        // 3. Bölmeler Tablosu
        Schema::create('bolmeler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cihaz_id')->nullable();
            $table->integer('bolme_no');
            $table->integer('yesil_led_pin')->nullable();
            $table->integer('kirmizi_led_pin')->nullable();
            $table->integer('buton_pin_no')->nullable();
            $table->timestamp('olusturulma_tarihi')->nullable();
            $table->timestamp('guncellenme_tarihi')->nullable();
        });

        // 4. Zamanlamalar Tablosu (gunler sütunu doğrudan buraya eklendi)
        Schema::create('zamanlamalar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bolme_id');
            $table->unsignedBigInteger('ilac_id');
            $table->time('alinacak_saat');
            $table->json('gunler')->nullable(); // Json formatında günleri tutacak
            $table->boolean('aktif_mi')->default(0);
            $table->timestamp('olusturulma_tarihi')->nullable();
            $table->timestamp('guncellenme_tarihi')->nullable();
        });

        // 5. Sistem Kayıtları Tablosu
        Schema::create('sistem_kayitlari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cihaz_id')->nullable();
            $table->unsignedBigInteger('bolme_id')->nullable();
            $table->string('ilac_adi')->nullable();
            $table->string('dozaj')->nullable();
            $table->time('planlanan_saat')->nullable();
            $table->string('durum')->nullable();
            $table->timestamp('islem_zamani')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sistem_kayitlari');
        Schema::dropIfExists('zamanlamalar');
        Schema::dropIfExists('bolmeler');
        Schema::dropIfExists('ilaclar');
        Schema::dropIfExists('cihazlar');
    }
};
