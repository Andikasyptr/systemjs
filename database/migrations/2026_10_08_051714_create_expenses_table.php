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
        Schema::create('expenses', function (Blueprint $t) {
        $t->id();
        $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
        $t->foreignId('created_by')->constrained('users');
        $t->date('spent_at');
        $t->string('category');
        $t->string('description');
        $t->unsignedBigInteger('amount');
        $t->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
