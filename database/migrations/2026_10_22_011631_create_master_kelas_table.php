<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kelas', function (Blueprint $table) {
            $table->increments('id_kelas');
            $table->unsignedInteger('id_jurusan');
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->string('kode_rombel', 10);
            $table->string('nama_kelas', 30)->unique(); // Contoh: XII RPL 1
            $table->timestamps();

            $table->foreign('id_jurusan')
                  ->references('id_jurusan')
                  ->on('master_jurusan')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kelas');
    }
};