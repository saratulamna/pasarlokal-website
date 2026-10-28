<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umkms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('nama_toko', 150);
            $table->string('slug')->unique();

            $table->text('deskripsi')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();

            $table->string('telepon', 30)->nullable();
            $table->text('alamat')->nullable();

            $table->string('status', 30)
                ->default('menunggu_verifikasi')
                ->index();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umkms');
    }
};
