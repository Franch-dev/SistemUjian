<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_mapel', function (Blueprint $table) {
            $table->increments('id_mapel');
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->unsignedTinyInteger('nilai_kkm')->default(75);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_mapel');
    }
};