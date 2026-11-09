<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('murid', function (Blueprint $table) {
            $table->increments('id_murid');
            $table->unsignedInteger('id_kelas');
            $table->string('nis', 30)->unique();
            $table->string('nama_lengkap', 150)->index();
            $table->string('password_hash', 255);
            $table->string('password_cetak', 50)->nullable(); // Untuk cetak kartu peserta ujian
            $table->timestamps();

            $table->foreign('id_kelas')
                  ->references('id_kelas')
                  ->on('master_kelas')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('murid');
    }
};