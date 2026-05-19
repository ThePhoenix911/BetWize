<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


// IMPORTANT: league_id has been REMOVED from this table.
// WHY: A club can be relegated or promoted between seasons.
//      If we store league_id directly on the club, we lose history.
//      "Where was this club in 2023?" becomes unanswerable.
//      Instead, we use a PIVOT TABLE (club_league) that stores
//      the club_id, league_id, AND the season year together.
//      This is explained in Migration 4.

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();

            // WHAT: The club's full name. e.g. "Orlando Pirates"
            // WHY: Required field — every club must have a name.
            $table->string('name');

            // WHAT: 3-letter abbreviation. e.g. "ORL", "KZC"
            // WHY: nullable() because some clubs (especially lower-league African clubs)
            //      don't have official short codes in API-Football's data.
            $table->string('short_code', 10)->nullable();

            // WHAT: URL to the club's badge/logo image from API-Football's CDN.
            // WHY: We don't download and host the images ourselves — too expensive on storage.
            //      We just save the URL and the browser loads the image directly from API-Football.
            $table->string('logo_url')->nullable();

            // WHAT: API-Football's unique ID for this club.
            // HOW: unique() + nullable() — same pattern as leagues.
            // WHY: This is our bridge to the outside world.
            //      When ImportFixtures finds "home_team_id: 1001" in the JSON,
            //      it does Club::where('api_id', 1001)->first() to get our local Club record.
            $table->unsignedBigInteger('api_id')->unique()->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
