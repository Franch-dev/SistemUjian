<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_sekolah', function (Blueprint $table) {
            $table->increments('id_profil');
            $table->string('nama_sekolah', 150)->default('SMK Negeri 1 Kota Bekasi');
            $table->string('npsn', 20)->default('20223136');
            $table->string('logo_path', 255)->nullable();
            $table->string('tahun_ajaran', 20);
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil');
            $table->string('nama_kepala_sekolah', 150)->nullable();
            $table->text('alamat_sekolah')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_sekolah');
    }
};