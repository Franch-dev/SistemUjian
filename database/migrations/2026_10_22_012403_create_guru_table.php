<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->increments('id_guru');
            $table->string('kode_guru', 50)->unique(); // Format: 6 huruf nama + 4 angka acak
            $table->string('nama_lengkap', 150);
            $table->string('nip', 30)->nullable();
            $table->string('password_hash', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};