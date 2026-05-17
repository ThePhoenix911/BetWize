<?php
// app/Models/League.php

namespace App\Models;
// WHAT: Declares which "namespace" (folder group) this class belongs to.
// HOW: App\Models maps to the app/Models/ folder.
// WHY: PHP needs namespaces to avoid class name collisions.
//      Without it, if you have two classes named 'League' in different folders, PHP gets confused.
// VANILLA EQUIVALENT: It's like saying "this file lives in the Models drawer of the App cabinet."

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
// WHAT: Imports (makes available) specific classes from Laravel's framework.
// HOW: 'use' is PHP's way of saying "I want to use this class by its short name."
//      Without this, you'd have to write the full path every time:
//      class League extends \Illuminate\Database\Eloquent\Model { ... }
// WHY: Cleaner, shorter, standard PHP practice.

class League extends Model
// WHAT: Defines the League class.
// HOW: 'extends Model' means League INHERITS everything from Laravel's base Model class.
//      That's how $league->save(), League::find(), League::all() etc. all work —
//      they're inherited from the parent Model class. You didn't write them.
// VANILLA EQUIVALENT: Like inheriting from a base DatabaseHelper class that already
//      knows how to talk to PDO — you just add your specific table logic on top.
{
    // WHAT: The list of columns allowed to be mass-assigned.
    // HOW: When you call League::create(['name' => 'PSL', 'country' => 'SA', ...]),
    //      Laravel only saves columns listed here. Everything else is silently ignored.
    // WHY: Security. Imagine a user submits a form with an extra field 'is_admin=1'.
    //      Without $fillable, Laravel would save that to the database.
    //      With $fillable, it's ignored unless 'is_admin' is explicitly listed.
    // VANILLA EQUIVALENT: Like a whitelist of POST variables you accept:
    //      $allowed = ['name', 'country', 'logo_url', 'api_id', 'short_code'];
    //      $data = array_intersect_key($_POST, array_flip($allowed));
    protected $fillable = ['name', 'country', 'logo_url', 'api_id', 'short_code'];

    // ==================== RELATIONSHIPS ====================

    public function fixtures(): MorphMany
        // WHAT: Defines the relationship between League and Fixture.
        // HOW: MorphMany is the "one side" of a polymorphic one-to-many relationship.
        //      It tells Laravel: "A League can have many Fixtures, and those fixtures
        //      identify this League using the 'competition' polymorphic columns."
        // WHY: Without this, you'd have to write raw SQL every time you want a league's fixtures.
        //      With this, you just call $league->fixtures and Laravel handles the query.
        // VANILLA EQUIVALENT:
        //      SELECT * FROM fixtures
        //      WHERE competition_id = {$this->id}
        //      AND competition_type = 'App\Models\League'
    {
        return $this->morphMany(Fixture::class, 'competition');
        // 'competition' matches the morphs('competition') name in the migration.
        // Laravel uses this to know the column names are competition_id and competition_type.
    }

    public function clubs(): HasMany
        // WHAT: All clubs that have EVER been in this league (any season).
        // HOW: HasMany means "one League has many club_league pivot rows."
        //      But we use the pivot table, not a direct column on clubs.
        //      See the Club model's leagues() BelongsToMany for the full picture.
        // WHY: Lets you call $league->clubs to get all clubs for this league.
        // VANILLA EQUIVALENT:
        //      SELECT clubs.* FROM clubs
        //      JOIN club_league ON clubs.id = club_league.club_id
        //      WHERE club_league.league_id = {$this->id}
    {
        return $this->hasMany(Club::class);
    }
}
