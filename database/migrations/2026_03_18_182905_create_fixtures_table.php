<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



// WHAT: The fixtures table stores every match — past, present, and future.
// KEY CONCEPT — POLYMORPHIC RELATIONSHIP:
//   A fixture (match) can belong to EITHER a League OR a Cup.
//   Approach 1 (bad): Two nullable columns — league_id and cup_id.
//     Problem: One will always be null. Confusing. Hard to query.
//   Approach 2 (good — what we use): Polymorphism.
//     Two columns: competition_id (the ID) + competition_type (which table).
//     Example row: competition_id=3, competition_type='App\Models\League'
//     Another row: competition_id=1, competition_type='App\Models\Cup'
//   Laravel reads both columns together and knows exactly which model to load.
//
// VANILLA EQUIVALENT:
//   It's like having a "reference" column that says:
//   "Look up ID 3 in the leagues table" or "Look up ID 1 in the cups table"
//   based on what's in the type column.

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();

            // WHAT: Creates TWO columns: competition_id and competition_type.
            // HOW: morphs('competition') is Laravel shorthand for:
            //      $table->unsignedBigInteger('competition_id');
            //      $table->string('competition_type');
            //      Plus an index on both columns together for fast queries.
            // WHY: 'competition' is the RELATIONSHIP NAME — it names the two columns.
            //      competition_id holds the actual ID (e.g. 3)
            //      competition_type holds the model class (e.g. 'App\Models\League')
            //      Together they tell Laravel: "Go fetch League with id=3"
            $table->morphs('competition');

            // WHAT: The home team's club ID.
            // HOW: foreignId()->constrained('clubs') adds a foreign key to the clubs table.
            //      We pass 'clubs' explicitly because Laravel can't guess which table
            //      from a column name like 'home_team_id' (it would guess 'home_teams').
            // WHY: cascadeOnDelete() means if a club is deleted, its fixtures go too.
            //      Without this, deleting a club would leave orphan rows referencing a ghost club.
            $table->foreignId('home_team_id')->constrained('clubs')->cascadeOnDelete();

            // WHAT: The away team's club ID. Same pattern as home_team_id.
            $table->foreignId('away_team_id')->constrained('clubs')->cascadeOnDelete();

            // WHAT: The scheduled date and time of the match.
            // HOW: dateTime() creates a DATETIME column (date + time together).
            // WHY: We need both date AND time (e.g. 2025-03-29 15:30:00)
            //      because multiple matches can happen on the same day.
            $table->dateTime('match_at');

            // WHAT: The current state of the match.
            // HOW: default('NS') means new rows automatically get 'NS' if status isn't provided.
            // WHY: API-Football status codes we care about:
            //      'NS'  = Not Started (scheduled future match)
            //      '1H'  = First Half (live)
            //      'HT'  = Half Time (live)
            //      '2H'  = Second Half (live)
            //      'FT'  = Full Time (finished)
            //      'PST' = Postponed
            $table->string('status')->default('NS');

            // WHAT: The home team's goal count.
            // HOW: integer() creates an INT column. nullable() allows NULL.
            // WHY: Before a match starts, there is no score — NULL is correct.
            //      During and after the match, this gets updated to a number.
            //      0 is different from NULL: 0 means "they scored zero goals",
            //      NULL means "the match hasn't started yet".
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();

            // WHAT: API-Football's unique ID for this fixture.
            // HOW: unique() ensures no two rows have the same api_id.
            // WHY: This is the KEY to the entire import system.
            //      updateOrCreate uses api_id to decide: "does this fixture already exist?"
            //      If yes → update it (e.g. score changed from null to 2-1)
            //      If no  → create it (new fixture we haven't seen before)
            //      Without this, we'd create duplicate fixtures on every import run.
            $table->unsignedBigInteger('api_id')->unique()->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
