<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


// WHAT: Stores which users follow which things (clubs, leagues, cups).
// WHY POLYMORPHIC:
//   A user might follow:
//   - Orlando Pirates (a Club)
//   - The PSL (a League)
//   - The CAF Champions League (a Cup)
//   Instead of THREE tables (club_follows, league_follows, cup_follows),
//   we use ONE table with polymorphic columns.
//   followable_id  = the ID of the thing being followed
//   followable_type = which model type it is
//
// REAL DATA EXAMPLE:
//   user_id=1, followable_id=5, followable_type='App\Models\Club'     → User 1 follows Club 5
//   user_id=1, followable_id=2, followable_type='App\Models\League'   → User 1 follows League 2
//   user_id=1, followable_id=1, followable_type='App\Models\Cup'      → User 1 follows Cup 1

return new class extends Migration {
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();

            // WHAT: Which user is doing the following.
            // HOW: foreignId('user_id')->constrained() links to the users table.
            //      cascadeOnDelete() means if a user deletes their account,
            //      all their follows are deleted too. Clean data.
            // WHY: A follow without a user makes no sense.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // WHAT: Creates followable_id and followable_type columns.
            // HOW: morphs('followable') is shorthand for:
            //      unsignedBigInteger('followable_id')
            //      string('followable_type')
            //      Plus a combined index for fast lookups.
            // WHY: 'followable' names the relationship.
            //      followable_id = the ID of the Club/League/Cup being followed
            //      followable_type = 'App\Models\Club', 'App\Models\League', or 'App\Models\Cup'
            $table->morphs('followable');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
