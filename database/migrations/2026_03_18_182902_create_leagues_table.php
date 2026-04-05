<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// WHAT: This class defines how to CREATE and DESTROY the leagues table.
// HOW: It extends Migration, giving it up() and down() methods Laravel calls.
// WHY: up() runs when you migrate. down() runs when you rollback.
//      Having both means you can always undo a change safely.
return new class extends Migration
{
    public function up(): void
    {
        // WHAT: Creates the 'leagues' table in the database.
        // HOW: Schema::create() takes the table name and a closure.
        //      The $table object inside the closure is a Blueprint —
        //      it's Laravel's way of describing columns in PHP instead of SQL.
        // VANILLA EQUIVALENT: Like writing CREATE TABLE leagues (...) in SQL.
        Schema::create('leagues', function (Blueprint $table) {

            // WHAT: Creates an auto-incrementing primary key column called 'id'.
            // HOW: id() is shorthand for BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY.
            // WHY: Every table needs a unique identifier for each row.
            //      Eloquent models expect a column named 'id' by default.
            $table->id();

            // WHAT: A VARCHAR(255) column called 'name'. Cannot be null.
            // WHY: Every league has a name (e.g. "Betway Premiership").
            $table->string('name');

            // WHAT: Stores which country the league belongs to (e.g. "South Africa").
            $table->string('country');

            // WHAT: URL string pointing to the league's logo image.
            // HOW: nullable() means this column CAN be NULL in the database.
            // WHY: We might create a league record before we have its logo URL.
            //      nullable() prevents a crash when logo_url is missing.
            $table->string('logo_url')->nullable();

            // WHAT: Short abbreviation for the league (e.g. "PSL", "EPL").
            // HOW: string('short_code', 10) limits the column to 10 characters max.
            // WHY: Used in UI badges and compact displays.
            $table->string('short_code', 10)->nullable();

            // WHAT: The ID this league has in the API-Football system.
            // HOW: unsignedBigInteger means a positive whole number (no negatives).
            //      unique() adds a database constraint — no two rows can have the same api_id.
            //      nullable() allows leagues we create manually without an API match.
            // WHY: This is the anchor between OUR database and API-Football's database.
            //      When we import fixtures, we look up leagues by api_id, not by name.
            //      Name strings can change ("Betway Prem" vs "PSL") — api_id never does.
            $table->unsignedBigInteger('api_id')->unique()->nullable();

            // WHAT: Adds created_at and updated_at DATETIME columns.
            // HOW: Laravel manages these automatically — you never set them manually.
            // WHY: Useful for debugging (when was this record created?) and sorting.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // WHAT: Deletes the entire leagues table.
        // WHY: Called by `php artisan migrate:rollback` to undo this migration.
        Schema::dropIfExists('leagues');
    }
};
