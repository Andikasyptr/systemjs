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
        Schema::create('customers', function (Blueprint $t) {
        $t->id();
        $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
        $t->string('member_code')->unique();
        $t->string('name');
        $t->string('phone', 20);
        $t->text('address')->nullable();
        $t->unsignedInteger('points')->default(0);
        $t->unsignedBigInteger('total_spent')->default(0);
        $t->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
