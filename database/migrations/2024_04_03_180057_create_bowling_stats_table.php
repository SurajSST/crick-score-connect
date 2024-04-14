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
        Schema::create('bowling_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('match_id')->constrained()->onDelete('cascade');
            $table->foreignId('innings_id')->constrained('innings')->onDelete('cascade');
            $table->decimal('overs_bowled', 5, 2)->default(0);
            $table->integer('runs_conceded')->default(0);
            $table->integer('wickets_taken')->default(0);
            $table->integer('maidens')->default(0);
            $table->decimal('economy_rate', 8, 2)->default(0);
            $table->enum('status', ['bowling', 'not_bowling'])->default('not_bowling');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bowling_stats');
    }
};
