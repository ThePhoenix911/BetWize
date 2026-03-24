<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    /********** Allowed Columns **********/
    protected $fillable = ['name', 'league_id', 'logo_url', 'short_code', 'api_id'];


    /********** Relationships - who it shares data with **********/

    // Returns the League that this Club belongs to
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    // Returns the Fixture where this Club is the home team
    public function homeFixtures(): HasMany
    {
        // A Club can have many fixtures, and a fixture can belong to one club
        // used the Club class because we want to access the Club that is the home team in the fixture
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    // Returns the Fixture where this Club is the away team
    public function awayFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }
}
