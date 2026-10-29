<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $t) {
        $t->id();
        $t->string('invoice_no')->unique();
        $t->foreignId('customer_id')->constrained();
        $t->foreignId('branch_id')->constrained();
        $t->foreignId('created_by')->constrained('users');
        $t->string('status')->default('diterima');          // diterima, diproses, selesai, diambil, batal
        $t->string('payment_status')->default('belum_bayar'); // belum_bayar, sebagian, lunas
        $t->unsignedBigInteger('subtotal')->default(0);
        $t->unsignedBigInteger('discount')->default(0);
        $t->unsignedBigInteger('total')->default(0);
        $t->unsignedBigInteger('paid_amount')->default(0);
        $t->date('estimated_done_at')->nullable();
        $t->text('notes')->nullable();
        $t->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
