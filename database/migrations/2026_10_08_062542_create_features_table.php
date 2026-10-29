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
        Schema::create('features', function (Blueprint $t) {
        $t->id();
        $t->string('icon', 10)->default('✨');
        $t->string('title');
        $t->string('description');
        $t->unsignedInteger('sort_order')->default(0);
        $t->boolean('is_active')->default(true);
        $t->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
