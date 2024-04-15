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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team1_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('team2_id')->constrained('teams')->onDelete('cascade');
            $table->dateTime('date');
            $table->string('time');
            $table->string('key')->unique()->nullable();
            $table->foreignId('toss_winner_id')->constrained('teams')->onDelete('cascade');
            $table->boolean('toss_winner_batting_first')->default(true); // New column to determine if the toss winner decides to bat first
            $table->string('venue');
            $table->integer('overs');
            $table->integer('players_per_team');
            $table->boolean('isGameFinished')->default(false);
            $table->string('finishedMessage')->nullable()->default('Message');
            $table->boolean('isGameCanceled')->default(false);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('target')->default(0);
            $table->decimal('CRR', 8, 2)->default(0);
            $table->decimal('RRR', 8, 2)->default(0);
            $table->json('extras')->default('{"byes": 0, "legByes": 0, "wide": 0, "noBall": 0, "penalty": 0}');
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
