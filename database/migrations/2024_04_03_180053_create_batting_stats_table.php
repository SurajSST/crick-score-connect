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
        Schema::create('batting_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('match_id')->constrained()->onDelete('cascade');
            $table->foreignId('innings_id')->constrained('innings')->onDelete('cascade');
            $table->integer('runs_scored');
            $table->integer('fours');
            $table->integer('sixes');
            $table->decimal('strike_rate', 8, 2);
            $table->integer('balls_faced');
            // Add other batting stats-related columns here
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batting_stats');
    }
};
