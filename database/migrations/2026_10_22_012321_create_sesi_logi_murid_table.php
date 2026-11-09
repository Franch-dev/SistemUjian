<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_login_murid', function (Blueprint $table) {
            $table->increments('id_sesi_login');
            $table->unsignedInteger('id_murid')->unique(); // 1 Murid maksimal 1 sesi aktif
            $table->string('session_token', 255)->nullable();
            $table->boolean('is_login_locked')->default(false); // 0 = Terbuka, 1 = Terkunci
            $table->dateTime('waktu_login')->nullable();
            $table->timestamps();

            $table->foreign('id_murid')
                  ->references('id_murid')
                  ->on('murid')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_login_murid');
    }
};