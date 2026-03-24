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
        Schema::create('club_league', function (Blueprint $table) {
            $table->id();

            $table->foreignId('club_id')
                ->constrained()        // links to the 'clubs' table automatically
                ->cascadeOnDelete();   // if a club is deleted, remove all its league history too
            // WHY: we need to know WHICH club is in WHICH league

            $table->foreignId('league_id')
                ->constrained()        // links to the 'leagues' table automatically
                ->cascadeOnDelete();   // if a league is deleted, remove all membership records
            // WHY: we need to know which league the club belongs to for that season

            $table->unsignedSmallInteger('season');
            // stores the year e.g. 2024 or 2025
            // unsignedSmallInteger is used instead of integer because
            // a year will never be negative and smallInteger uses less storage
            // WHY: this is the key column that makes relegation/promotion trackable
            // without it we'd only know where a club is NOW, not where they were before

            $table->unique(['club_id', 'league_id', 'season']);
            // prevents the same club being added to the same league twice in the same season
            // e.g. you can't have Sundowns in PSL 2025 twice
            // this is a composite unique constraint - all three columns together must be unique

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_league');
    }
};
