<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_nilai_ujian', function (Blueprint $table) {
            $table->increments('id_hasil_nilai');
            $table->unsignedInteger('id_ujian_murid')->unique();
            $table->unsignedSmallInteger('jumlah_benar')->default(0);
            $table->float('nilai_akhir')->default(0);
            $table->enum('status_kelulusan', ['Lulus', 'Remedial']);
            $table->dateTime('waktu_selesai')->useCurrent();
            $table->timestamps();

            $table->foreign('id_ujian_murid')
                  ->references('id_ujian_murid')
                  ->on('ujian_murid')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_nilai_ujian');
    }
};