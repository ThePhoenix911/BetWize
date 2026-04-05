<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


// WHAT: This is a PIVOT TABLE — a bridge table that connects clubs to leagues.
// WHY PIVOT TABLES EXIST:
//   In vanilla PHP, if a club belonged to exactly ONE league forever, you'd just
//   add a league_id column on the clubs table. Simple.
//   But clubs get relegated and promoted. A club can be in PSL one year and
//   GladAfrica Championship the next. We need to record BOTH relationships.
//   A pivot table handles many-to-many relationships:
//   - One club can belong to many leagues (across seasons)
//   - One league can have many clubs (across seasons)
//
// VANILLA EQUIVALENT:
//   SELECT * FROM club_league WHERE club_id = 5 AND season = 2025;
//   That gives you which league Club 5 was in during the 2025 season.

return new class extends Migration
{
    public function up(): void
    {
        // NAMING CONVENTION: Laravel expects pivot tables to be named
        // using the two model names in ALPHABETICAL ORDER, singular, separated by underscore.
        // club + league = club_league (c comes before l alphabetically)
        Schema::create('club_league', function (Blueprint $table) {
            $table->id();

            // WHAT: Foreign key pointing to the clubs table.
            // HOW: foreignId() creates an unsignedBigInteger column.
            //      constrained() automatically adds: FOREIGN KEY (club_id) REFERENCES clubs(id)
            //      cascadeOnDelete() means: if the club is deleted, delete this row too.
            // WHY: We can't have a membership record for a club that no longer exists.
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();

            // WHAT: Foreign key pointing to the leagues table.
            // WHY: Same reasoning — the league must exist for this record to be valid.
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();

            // WHAT: The year this club-league membership applies to. e.g. 2025.
            // HOW: unsignedSmallInteger stores whole numbers up to 65,535.
            //      A year like 2025 fits comfortably. No need for a full integer.
            // WHY: THIS is the column that makes relegation trackable.
            //      Without season, you only know where a club IS now.
            //      With season, you know where they were in any given year.
            $table->unsignedSmallInteger('season');

            // WHAT: Composite unique constraint across ALL THREE columns together.
            // HOW: unique([]) with an array enforces that the COMBINATION must be unique.
            // WHY: Sundowns can be in PSL in 2024 AND in PSL in 2025 — both valid rows.
            //      But Sundowns cannot be in PSL TWICE in 2025 — that's a duplicate.
            //      This constraint prevents the import job from creating that duplicate.
            //      Without it, ImportTeams would crash on the second run.
            $table->unique(['club_id', 'league_id', 'season']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_league');
    }
};
