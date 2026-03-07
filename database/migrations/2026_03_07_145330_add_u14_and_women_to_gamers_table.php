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
        Schema::table('gamers', function (Blueprint $table) {
            $table->boolean('u14')->default(false)->after('id'); 
            $table->boolean('women')->default(false)->after('u14');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gamers', function (Blueprint $table) {
            $table->dropColumn(['u14', 'women']);
        });
    }
};
