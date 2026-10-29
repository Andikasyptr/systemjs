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
        Schema::create('order_items', function (Blueprint $t) {
        $t->id();
        $t->foreignId('order_id')->constrained()->cascadeOnDelete();
        $t->foreignId('service_id')->constrained();
        $t->string('item_name');                 // contoh: "Nike Air Force 1 putih"
        $t->unsignedInteger('qty')->default(1);
        $t->unsignedInteger('price');
        $t->unsignedBigInteger('subtotal');
        $t->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
