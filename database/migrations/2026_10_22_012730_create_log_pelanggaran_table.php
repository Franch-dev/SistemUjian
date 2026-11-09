<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_pelanggaran', function (Blueprint $table) {
            $table->increments('id_log_pelanggaran');
            $table->unsignedInteger('id_ujian_murid');
            $table->string('jenis_pelanggaran', 100); // Contoh: Keluar Mode Fullscreen, Pindah Tab Browser
            $table->dateTime('waktu_pelanggaran')->useCurrent();
            $table->timestamps();

            $table->foreign('id_ujian_murid')
                  ->references('id_ujian_murid')
                  ->on('ujian_murid')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_pelanggaran');
    }
};