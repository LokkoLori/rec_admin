<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->enum('status', ['announced', 'started', 'cancelled', 'finished'])->default('announced');
        });

        Schema::table('competition_days', function (Blueprint $table) {
            $table->enum('status', ['announced', 'started', 'cancelled', 'finished'])->default('announced');
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->enum('status', ['applyed', 'accepted', 'revoked', 'disqualified', 'finished'])->default('applyed');
        });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('competition_days', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
