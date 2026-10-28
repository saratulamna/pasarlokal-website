<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('umkm_id')
                ->constrained('umkms')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('nama', 180);
            $table->string('slug')->unique();

            $table->longText('deskripsi')->nullable();

            $table->decimal('harga', 15, 2);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('terjual')->default(0);

            $table->string('gambar')->nullable();

            $table->string('status', 20)
                ->default('draft')
                ->index();

            $table->timestamps();

            $table->index(['umkm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
