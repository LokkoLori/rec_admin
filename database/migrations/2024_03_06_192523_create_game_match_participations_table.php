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
        Schema::create('game_match_participations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_match_id');
            $table->unsignedBigInteger('player_id');
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->foreign('game_match_id')->references('id')->on('game_matches')->onDelete('cascade');
            $table->foreign('player_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_match_participations');
    }
};
