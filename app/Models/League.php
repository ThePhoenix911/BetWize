<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class League extends Model
{
    /************ ALLOWED COLUMNS FOR MASS ASSIGNMENT *************/
    // Mass Assignment Protection
    // Allows us to create a League using the create() method
    // Without this, we would need to specify each column when creating a League
    // Laravel makes it impossible to pass data to the columns if they are not explicitly mentioned in the $fillable array
    // This is a security measure to prevent unwanted data from being inserted into the database
    // It's basically a way of saying "only allow these columns to be mass-assigned"
    protected $fillable = ['name', 'country', 'logo_url', 'api_id'];


    /********** Relationships - who it shares data with **********/


    // The idea here is to enables us to access all the Fixtures for a League from the League model
    // This method creates a polymorphic relationship between League and Fixture
    // and returns a collection of Fixture objects with the League as the 'competition'
    // We use 'morphMany' because a League can have many Fixtures, and a Fixture can belong to many Leagues
    // 'competition' is the name of the relationship, and 'Fixture' is the related model
    // This enables us to easily access all the Fixtures for a League
    public function fixtures(): MorphMany
    {
        // A league can have many fixtures, and a fixture can belong to one league
        return $this->morphMany(Fixture::class, 'competition');
    }


    // This enables us to access all the Clubs of a specific League from the League model
    // It also includes the season column from the pivot table
    // This is a BelongsToMany relationship because a League can have many Clubs
    // and a Club can belong to many Leagues across seasons
    // The pivot table is 'club_league' and it has a 'season' column
    // This allows us to easily track which clubs were in which league during which season
    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_league')->withPivot('season')->withTimestamps();
    }
}
