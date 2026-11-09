<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_murid', function (Blueprint $table) {
            $table->increments('id_jawaban');
            $table->unsignedInteger('id_ujian_murid');
            $table->unsignedInteger('id_soal');
            $table->enum('pilihan_jawaban', ['A', 'B', 'C', 'D', 'E'])->nullable();
            $table->boolean('status_ragu')->default(false);
            $table->dateTime('waktu_autosave')->nullable();
            $table->timestamps();

            // Kunci unik gabungan untuk fitur updateOrCreate (Auto-Save)
            $table->unique(['id_ujian_murid', 'id_soal'], 'unik_jawaban_soal_murid');

            $table->foreign('id_ujian_murid')
                  ->references('id_ujian_murid')
                  ->on('ujian_murid')
                  ->onDelete('cascade');

            $table->foreign('id_soal')
                  ->references('id_soal')
                  ->on('bank_soal')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_murid');
    }
};