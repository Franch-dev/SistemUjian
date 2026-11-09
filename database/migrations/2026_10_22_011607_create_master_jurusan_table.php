<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_jurusan', function (Blueprint $table) {
            $table->increments('id_jurusan');
            $table->string('kode_jurusan', 15)->unique(); // Auto-uppercase: RPL, TKJ, DKV, AKL
            $table->string('nama_jurusan', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jurusan');
    }
};