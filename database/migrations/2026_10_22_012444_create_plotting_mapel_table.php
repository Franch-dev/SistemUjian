<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plotting_mapel', function (Blueprint $table) {
            $table->increments('id_plotting');
            $table->unsignedInteger('id_guru');
            $table->unsignedInteger('id_mapel');
            $table->unsignedInteger('id_kelas');
            $table->timestamps();

            // Mencegah duplikasi mapel yang sama di kelas yang sama
            $table->unique(['id_mapel', 'id_kelas'], 'unik_mapel_per_kelas');

            $table->foreign('id_guru')
                  ->references('id_guru')
                  ->on('guru')
                  ->onDelete('cascade');

            $table->foreign('id_mapel')
                  ->references('id_mapel')
                  ->on('master_mapel')
                  ->onDelete('cascade');

            $table->foreign('id_kelas')
                  ->references('id_kelas')
                  ->on('master_kelas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plotting_mapel');
    }
};