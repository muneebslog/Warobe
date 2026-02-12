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
        Schema::table('clothing_items', function (Blueprint $table) {
            $table->string('color_family')->nullable()->after('color');
            $table->string('color_hex', 7)->nullable()->after('color_family');
            $table->index('color_family');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clothing_items', function (Blueprint $table) {
            $table->dropIndex(['color_family']);
            $table->dropColumn(['color_family', 'color_hex']);
        });
    }
};
