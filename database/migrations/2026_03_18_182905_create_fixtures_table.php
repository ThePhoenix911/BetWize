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
        Schema::create('fixtures', function (Blueprint $table) {
            // HIGHEST LEVEL: The Primary Key
            $table->id();

            // MID LEVEL: The Competition Link (Polymorphism)
            // Instead of separate 'league_id' and 'cup_id' columns, we use 'morphs' (a polymorphic relationship)
            // WHY: This creates TWO Columns: 'competition_id' and 'competition_type'
            // HOW: It allows a fixture to belong to a 'League' or 'Cup' using the same logic
            // EXAMPLE: A fixture can belong to a League (with 'competition_type' as 'league') or a Cup (with 'competition_type' as 'cup')
            // We passed the 'competition' parameter to specify the relationship name
            // This will create 'competition_id' and 'competition_type' columns
            // 'competition_id' will either be a League or Cup ID, depending on 'competition_type'
            // 'competition_type' will either be 'App\Models\League' or 'App\Models\Cup'
            $table->morphs('competition');

            // MID LEVEL: The team relationships (Foreign Keys)
            // 'home_team_id' and 'away_team_id' point to the 'id' column in the 'clubs' table
            // WHY: 'constrained' ensures you can't have a match between clubs that don't exist/
            // 'cascadeOnDelete' ensures that if a club is deleted, all its fixtures are also deleted
            $table->foreignId('home_team_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('clubs')->cascadeOnDelete();

            // LOW LEVEL: Match Details
            // 'match_at' stores the date and time of the match
            $table->dateTime('match_at');

            // 'status' tracks the game state (e.g., 'NS' for not started, 'LIVE', 'FT' for finished)
            $table->string('status')->default('NS');


            // LOW LEVEL: Match Results
            // 'home_score' and 'away_score' store the scores of the match
            // WHY: These columns allow us to track the outcome of the match
            // EXAMPLE: If 'home_score' is 2 and 'away_score' is 1, the home team won
            // 'nullable' allows these to stay empty until the game starts
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();

            // 'api_id' stores the unique identifier for the fixture from the API
            // This is our Anchor to the outside world
            // We use this to check if we've already imported the match from the API
            // unsignedBigInteger ensures it's a positive number - we used the unsignedBigInteger method to create a positive integer column
            // The 'Scout' (API importer) will run every hour.
            // The unique constraint ensures we don't import the same match twice
            $table->unsignedBigInteger('api_id')->unique()->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
