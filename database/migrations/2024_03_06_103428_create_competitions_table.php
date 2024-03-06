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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competition_day_id');
            $table->unsignedBigInteger('game_id');
            $table->timestamps();

            $table->foreign('competition_day_id')->references('id')->on('competition_days')->onDelete('cascade');
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
