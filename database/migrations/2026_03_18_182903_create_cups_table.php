<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cups', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // e.g. "CAF Champions League"
            $table->string('country');      // e.g. "Africa" or "South Africa"
            $table->string('logo_url')->nullable();

            // WHAT: API-Football's ID for this cup competition.
            // NOTE: This was MISSING in your original migration — add it.
            // WHY: Without api_id, we can't link imported fixtures to the correct cup.
            //      The Fixture model's polymorphic competition_id needs a real local ID,
            //      and we find that local ID by looking up the api_id from the import.
            $table->unsignedBigInteger('api_id')->unique()->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cups');
    }
};

