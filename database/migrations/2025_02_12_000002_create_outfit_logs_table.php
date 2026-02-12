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
        Schema::create('outfit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->foreignId('shirt_id')->nullable()->constrained('clothing_items')->nullOnDelete();
            $table->foreignId('pant_id')->nullable()->constrained('clothing_items')->nullOnDelete();
            $table->foreignId('shalwar_kameez_id')->nullable()->constrained('clothing_items')->nullOnDelete();
            $table->timestamp('worn_at');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outfit_logs');
    }
};
