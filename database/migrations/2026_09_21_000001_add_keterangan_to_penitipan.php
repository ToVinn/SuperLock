<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penitipan', function (Blueprint $table) {
            $table->string('keterangan', 255)->nullable()->after('guru_nama');
        });
    }

    public function down(): void
    {
        Schema::table('penitipan', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
