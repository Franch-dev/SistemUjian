<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_soal', function (Blueprint $table) {
            $table->increments('id_soal');
            $table->unsignedInteger('id_plotting');
            $table->text('pertanyaan');
            $table->string('path_gambar', 255)->nullable();
            $table->text('opsi_a');
            $table->text('opsi_b');
            $table->text('opsi_c');
            $table->text('opsi_d');
            $table->text('opsi_e');
            $table->enum('kunci_jawaban', ['A', 'B', 'C', 'D', 'E']);
            $table->unsignedSmallInteger('bobot_soal')->default(1);
            $table->timestamps();

            $table->foreign('id_plotting')
                  ->references('id_plotting')
                  ->on('plotting_mapel')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_soal');
    }
};