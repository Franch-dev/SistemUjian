<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujian_murid', function (Blueprint $table) {
            $table->increments('id_ujian_murid');
            $table->unsignedInteger('id_sesi');
            $table->unsignedInteger('id_murid');
            $table->boolean('pakta_integritas')->default(false);
            $table->boolean('fullscreen_lock')->default(false);
            $table->integer('sisa_waktu_detik')->default(0);
            $table->unsignedTinyInteger('jumlah_pelanggaran')->default(0);
            $table->enum('status_ujian', [
                'belum_mulai',
                'mengerjakan',
                'selesai_mandiri',
                'auto_submit_waktu',
                'auto_submit_pelanggaran',
                'diskualifikasi'
            ])->default('belum_mulai');
            $table->dateTime('waktu_mulai')->nullable();
            $table->timestamps();

            // 1 Murid hanya memiliki 1 sesi pengerjaan per Sesi Ujian
            $table->unique(['id_sesi', 'id_murid'], 'unik_ujian_per_murid');

            $table->foreign('id_sesi')
                  ->references('id_sesi')
                  ->on('sesi_ujian')
                  ->onDelete('cascade');

            $table->foreign('id_murid')
                  ->references('id_murid')
                  ->on('murid')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_murid');
    }
};