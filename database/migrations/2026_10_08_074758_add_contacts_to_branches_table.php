<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $t) {
            $t->string('whatsapp', 20)->nullable()->after('phone');
            $t->string('instagram', 50)->nullable()->after('whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $t) {
            $t->dropColumn(['whatsapp', 'instagram']);
        });
    }
};