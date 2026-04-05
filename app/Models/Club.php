<?php
// app/Models/Club.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    // NOTE: 'name', 'short_code', 'logo_url', 'api_id' are here.
    // NOTE: 'league_id' is NOT here — it no longer exists as a column.
    //       The club-league relationship lives in the pivot table.
    protected $fillable = ['name', 'short_code', 'logo_url', 'api_id'];

    // ==================== RELATIONSHIPS ====================

    public function leagues(): BelongsToMany
        // WHAT: All leagues this club has belonged to, across all seasons.
        // HOW: BelongsToMany defines a many-to-many relationship via the club_league pivot table.
        //      withPivot('season') tells Laravel to also load the 'season' column from the pivot.
        //      withTimestamps() lets Laravel manage created_at/updated_at on pivot rows.
        // WHY: Enables $club->leagues to return all a club's league memberships.
        //      Enables $club->leagues()->wherePivot('season', 2025)->first()
        //      to get exactly which league the club is in THIS season.
        // VANILLA EQUIVALENT:
        //      SELECT leagues.*, club_league.season
        //      FROM leagues
        //      JOIN club_league ON leagues.id = club_league.league_id
        //      WHERE club_league.club_id = {$this->id}
    {
        return $this->belongsToMany(League::class)
            ->withPivot('season')
            ->withTimestamps();
    }

    public function currentLeague(): ?League
        // WHAT: Returns the league this club is in RIGHT NOW (current year).
        // HOW: Builds on the leagues() relationship, filters by current year's season.
        //      The '?' before League means nullable return — can return null if not found.
        //      first() returns one result or null (never crashes on empty).
        // WHY: Common need — "what league is this club in today?"
        //      Without this helper you'd repeat the wherePivot query everywhere.
        // VANILLA EQUIVALENT:
        //      SELECT leagues.* FROM leagues
        //      JOIN club_league ON leagues.id = club_league.league_id
        //      WHERE club_league.club_id = {$this->id}
        //      AND club_league.season = YEAR(NOW())
        //      LIMIT 1
    {
        return $this->leagues()
            ->wherePivot('season', now()->year)
            ->first();
    }

    public function leagueInSeason(int $season): ?League
        // WHAT: Returns which league this club was in during a SPECIFIC past season.
        // WHY: Useful for H2H context — "were both clubs in the PSL in 2022?"
        //      Called as: $club->leagueInSeason(2022)
    {
        return $this->leagues()
            ->wherePivot('season', $season)
            ->first();
    }

    public function homeFixtures(): HasMany
        // WHAT: All fixtures where this club is the HOME team.
        // HOW: hasMany(Fixture::class, 'home_team_id') tells Laravel which foreign key to use.
        //      We MUST specify 'home_team_id' because Laravel would otherwise guess 'club_id',
        //      which doesn't exist on fixtures.
        // VANILLA EQUIVALENT:
        //      SELECT * FROM fixtures WHERE home_team_id = {$this->id}
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    public function awayFixtures(): HasMany
        // WHAT: All fixtures where this club is the AWAY team.
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }
}
