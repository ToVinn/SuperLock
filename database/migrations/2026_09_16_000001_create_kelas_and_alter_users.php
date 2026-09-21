<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->dropColumn(['name', 'email', 'email_verified_at', 'remember_token']);
            $table->string('nama', 100);
            $table->enum('role', ['admin', 'km', 'guru']);
            $table->foreignId('kelas_id')->nullable()->constrained('kelas');
            $table->string('username', 50)->nullable()->unique();
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropColumn(['nama', 'role', 'kelas_id', 'username']);
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });
        Schema::dropIfExists('kelas');
    }
};
