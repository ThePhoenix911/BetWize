<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    protected $fillable = ['name', 'short_code', 'logo_url', 'api_id'];
    // league_id removed from fillable because it no longer exists as a column
    // the club-league relationship is now managed through the pivot table

    /********** Relationships **********/

    public function leagues(): BelongsToMany
    {
        // BelongsToMany because a club can be in different leagues across seasons
        // e.g. PSL in 2024, GladAfrica in 2025 (if relegated)
        return $this->belongsToMany(League::class)
            ->withPivot('season')
            // withPivot() tells Laravel to also load the 'season' column
            // from the pivot table when we access this relationship
            // without this, we'd only get the club and league data, not the season
            ->withTimestamps();
        // withTimestamps() lets Laravel manage created_at/updated_at
        // on the pivot table rows automatically
    }

    public function currentLeague(): ?League
    {
        // Returns the league this club belongs to RIGHT NOW (current year)
        // Returns null if the club isn't currently assigned to any league
        // The '?' before League means the return type is nullable
        return $this->leagues()
            ->wherePivot('season', now()->year)
            // wherePivot() filters by a column on the pivot table
            // not on the leagues table itself
            // so this says: "find the league where the pivot season = this year"
            ->first();
        // first() returns one result or null - we only want one current league
    }

    public function leagueInSeason(int $season): ?League
    {
        // Returns which league this club was in during a SPECIFIC past season
        // Useful for historical H2H context e.g. "were both clubs in PSL in 2022?"
        // Called like: $club->leagueInSeason(2022)
        return $this->leagues()
            ->wherePivot('season', $season)
            ->first();
    }

    public function homeFixtures(): HasMany
    {
        // All fixtures where this club is the home team
        // 'home_team_id' tells Laravel which foreign key to use
        // because it's not the default 'club_id' that Laravel would guess
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    public function awayFixtures(): HasMany
    {
        // All fixtures where this club is the away team
        // same reason as above - we need to specify the foreign key explicitly
        return $this->hasMany(Fixture::class, 'away_team_id');
    }
}
