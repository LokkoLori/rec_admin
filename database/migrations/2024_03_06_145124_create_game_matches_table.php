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
        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competition_id');
            $table->unsignedBigInteger('game_station_id');
            $table->enum('status', ['waiting', 'started', 'cancelled', 'finished']);
            $table->timestamps();

            $table->foreign('competition_id')->references('id')->on('competitions');
            $table->foreign('game_station_id')->references('id')->on('game_stations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_matches');
    }
};
