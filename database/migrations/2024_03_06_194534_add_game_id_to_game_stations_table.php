<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('game_stations', function (Blueprint $table) {
            $table->unsignedBigInteger('game_id')->nullable()->after('id');
            $table->foreign('game_id')->references('id')->on('games')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('game_stations', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
            $table->dropColumn('game_id');
        });
    }
};
