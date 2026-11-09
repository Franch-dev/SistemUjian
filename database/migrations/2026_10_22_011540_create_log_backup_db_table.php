<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_backup_db', function (Blueprint $table) {
            $table->increments('id_backup');
            $table->unsignedInteger('id_admin');
            $table->string('nama_file_sql', 200);
            $table->float('ukuran_file_kb')->default(0);
            $table->dateTime('waktu_backup')->useCurrent();
            $table->timestamps();

            $table->foreign('id_admin')
                  ->references('id_admin')
                  ->on('admin')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_backup_db');
    }
};