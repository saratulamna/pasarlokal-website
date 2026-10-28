<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number', 40)->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('umkm_id')
                ->constrained('umkms')
                ->restrictOnDelete();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->string('status', 30)
                ->default('menunggu_pembayaran');

            $table->string('payment_status', 30)
                ->default('belum_bayar');

            $table->string('payment_method', 30)->nullable();

            $table->string('recipient_name', 150);
            $table->string('recipient_phone', 30);
            $table->text('shipping_address');
            $table->string('shipping_postal_code', 15)->nullable();

            $table->text('notes')->nullable();
            $table->text('shipping_notes')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['umkm_id', 'status']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
