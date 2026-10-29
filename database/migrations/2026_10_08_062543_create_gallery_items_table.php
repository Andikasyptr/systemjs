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
        Schema::create('gallery_items', function (Blueprint $t) {
        $t->id();
        $t->string('title');
        $t->string('caption')->nullable();
        $t->string('category', 20)->nullable();   // sepatu, tas, helm
        $t->string('image_path');
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
        Schema::dropIfExists('gallery_items');
    }
};
