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
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->string('short_code', 10)->nullable();
            // nullable() because not every club has an official short code
            // and we don't want the import to fail just because of a missing abbreviation

            $table->string('logo_url')->nullable();
            // nullable() because logos come from the API
            // and we may create a club record before the logo is fetched

            $table->unsignedBigInteger('api_id')->unique()->nullable();
            // api_id is the ID this club has on API-Football
            // unique() ensures we never accidentally import the same club twice
            // nullable() allows us to create local/test clubs without an API match

            // NOTE: league_id has been intentionally removed here
            // A club's league membership is now tracked in the club_league pivot table
            // with a season column - this handles relegation and promotion across seasons
            // without losing historical data

            $table->timestamps();
            // created_at and updated_at - Laravel manages these automatically
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
