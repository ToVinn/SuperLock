<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20)->unique();
            $table->string('nama', 100);
            $table->foreignId('kelas_id')->constrained('kelas');
        });

        Schema::create('penitipan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->date('tanggal');
            $table->enum('status', ['belum', 'kumpul', 'tidak', 'pinjam'])->default('belum');
            $table->time('jam_kumpul')->nullable();
            $table->time('jam_ambil')->nullable();
            $table->time('jam_pinjam')->nullable();
            $table->time('jam_kembali')->nullable();
            $table->string('km_nama', 100)->nullable();
            $table->string('guru_nama', 100)->nullable();
            $table->unique(['siswa_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penitipan');
        Schema::dropIfExists('siswa');
    }
};
