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
        Schema::table('entries', function (Blueprint $table) {

            $table->dropUnique(['user_id', 'competition_id']);

            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->unsignedBigInteger('gamer_id');
            $table->foreign('gamer_id')->references('id')->on('gamers');

            $table->unique(['gamer_id', 'competition_id']);
        });

        Schema::table('game_match_participations', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
            $table->dropColumn('player_id');

            $table->unsignedBigInteger('gamer_id');
            $table->foreign('gamer_id')->references('id')->on('gamers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropUnique(['gamer_id', 'competition_id']);

            $table->dropForeign(['gamer_id']);
            $table->dropColumn('gamer_id');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unique(['user_id', 'competition_id']);
        });

        Schema::table('game_match_participations', function (Blueprint $table) {
            $table->dropForeign(['gamer_id']);
            $table->dropColumn('gamer_id');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
