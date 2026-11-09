<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_ujian', function (Blueprint $table) {
            $table->increments('id_sesi');
            $table->unsignedInteger('id_plotting');
            $table->unsignedInteger('id_admin');
            $table->unsignedInteger('id_profil');
            $table->string('nama_sesi_ujian', 150);
            $table->date('tanggal_ujian');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedSmallInteger('durasi_menit')->default(90);
            $table->unsignedTinyInteger('batas_toleransi_anticheat')->default(3);
            $table->enum('status_sesi', ['terbuka', 'tertutup'])->default('tertutup');
            $table->enum('visibilitas_jadwal', ['tampil', 'tutup'])->default('tutup');
            $table->boolean('publikasi_nilai')->default(false);
            $table->string('nama_pengawas_ruang', 150)->nullable();
            $table->text('catatan_berita_acara')->nullable();
            $table->timestamps();

            $table->foreign('id_plotting')
                  ->references('id_plotting')
                  ->on('plotting_mapel')
                  ->onDelete('cascade');

            $table->foreign('id_admin')
                  ->references('id_admin')
                  ->on('admin')
                  ->onDelete('restrict');

            $table->foreign('id_profil')
                  ->references('id_profil')
                  ->on('profil_sekolah')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_ujian');
    }
};