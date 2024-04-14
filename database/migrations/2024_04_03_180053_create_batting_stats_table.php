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
            $table->integer('runs_scored')->default(0);
            $table->integer('fours')->default(0);
            $table->integer('sixes')->default(0);
            $table->decimal('strike_rate', 8, 2)->default(0);
            $table->integer('balls_faced')->default(0);
            $table->enum('status', ['striker', 'non-striker', 'bencher'])->default('bencher');
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
