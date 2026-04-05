<?php
// app/Support/StandingsCalculator.php

// WHAT: Calculates a league standings table from your fixtures data.
// WHY A SEPARATE CLASS:
//   You could put this logic in the Livewire component or the League model.
//   But standing calculation is complex enough to deserve its own home.
//   If standings calculation ever needs to change (extra points for away wins, etc.),
//   you change ONE class. Not a component. Not a model. One class.
// VANILLA EQUIVALENT: Like a pure function file that does one complex calculation.
// THINK OF IT AS: The "editor" in our newspaper analogy.
//   Raw match results (facts) → StandingsCalculator → Formatted table (meaning)

namespace App\Support;

use App\Models\League;
use Illuminate\Support\Collection;

class StandingsCalculator
{
    public function calculate(League $league, int $season): Collection
        // WHAT: Takes a league and season, returns a sorted standings table.
        // RETURNS: Laravel Collection (like an enhanced PHP array with extra methods).
    {
        $fixtures = $league->fixtures()
            ->where('status', 'FT')
            // WHAT: Filter to ONLY finished matches.
            // WHY: We can only calculate standings from completed matches.
            //      'FT' = Full Time (API-Football's code for a finished match).
            //      Including NS (Not Started) matches would be nonsense.

            ->with(['homeTeam', 'awayTeam'])
            // WHAT: EAGER LOADING — loads related models in one extra query.
            // WHY: Without this, accessing $fixture->homeTeam inside the foreach loop
            //      would trigger a SEPARATE database query for EACH fixture.
            //      380 fixtures = 760 extra queries. This is called the N+1 problem.
            //      with(['homeTeam', 'awayTeam']) loads ALL teams in 2 queries total.
            //      380 fixtures = still just 2 queries. Massive performance difference.
            // VANILLA EQUIVALENT:
            //      Without: SELECT * FROM clubs WHERE id = X (once per fixture)
            //      With:    SELECT * FROM clubs WHERE id IN (1,2,3,...all ids at once)

            ->get();
        // WHAT: Execute the query and return a Collection of Fixture objects.
        // WHY: Everything before ->get() builds the query. ->get() actually runs it.

        $standings = [];
        // WHAT: Empty array that we'll fill with club stats row by row.
        // HOW: Keyed by club_id so we can look up any club instantly.
        //      $standings[5] = ['played' => 10, 'won' => 7, ...]

        foreach ($fixtures as $fixture) {
            $this->initialiseClub($standings, $fixture->homeTeam);
            $this->initialiseClub($standings, $fixture->awayTeam);
            // WHAT: Ensures both clubs have a stats row before we start adding to it.
            // WHY: If a club's row doesn't exist when we try to increment it,
            //      PHP throws an "undefined index" error.
            //      initialiseClub() creates a zeroed row if it doesn't exist yet.

            $homeId    = $fixture->home_team_id;
            $awayId    = $fixture->away_team_id;
            $homeGoals = $fixture->home_score;
            $awayGoals = $fixture->away_score;

            // Always count the match as played for both teams.
            $standings[$homeId]['played']++;
            $standings[$awayId]['played']++;

            // Goals scored and conceded:
            // Home team scored home goals, conceded away goals.
            // Away team scored away goals, conceded home goals.
            $standings[$homeId]['goals_for']     += $homeGoals;
            $standings[$homeId]['goals_against']  += $awayGoals;
            $standings[$awayId]['goals_for']     += $awayGoals;
            $standings[$awayId]['goals_against']  += $homeGoals;

            if ($homeGoals > $awayGoals) {
                // Home win
                $standings[$homeId]['won']++;
                $standings[$homeId]['points'] += 3;
                $standings[$awayId]['lost']++;

            } elseif ($homeGoals < $awayGoals) {
                // Away win
                $standings[$awayId]['won']++;
                $standings[$awayId]['points'] += 3;
                $standings[$homeId]['lost']++;

            } else {
                // Draw — 1 point each
                $standings[$homeId]['drawn']++;
                $standings[$homeId]['points']++;
                $standings[$awayId]['drawn']++;
                $standings[$awayId]['points']++;
            }
        }

        foreach ($standings as &$row) {
            // WHAT: Calculate Goal Difference for each club after the main loop.
            // HOW: GD = Goals For minus Goals Against.
            //      '&$row' means we're modifying the original array, not a copy.
            // WHY: Calculated after the loop because we need the FINAL GF and GA totals.
            $row['goal_difference'] = $row['goals_for'] - $row['goals_against'];
        }

        return collect($standings)
            ->sortByDesc('points')
            ->sortByDesc('goal_difference')
            ->sortByDesc('goals_for')
            ->values();
        // WHAT: Convert to a Laravel Collection, sort by football's standard rules,
        //       and reset the keys to 0,1,2... (values() does the key reset).
        // WHY: $loop->iteration in the blade template needs sequential keys to show
        //      position numbers (1st, 2nd, 3rd...) correctly.
    }

    private function initialiseClub(array &$standings, $club): void
        // WHAT: Creates a zeroed stats row for a club if it doesn't have one yet.
        // HOW: isset() checks if the key exists. If it does, we return early (do nothing).
        //      If it doesn't, we create a new zeroed row.
        // WHY: Private because only THIS class calls it.
        //      '&$standings' means we're modifying the original array (pass by reference).
    {
        if (isset($standings[$club->id])) {
            return; // Already initialised — nothing to do
        }

        $standings[$club->id] = [
            'club'            => $club,    // Store the whole model for template access
            'played'          => 0,
            'won'             => 0,
            'drawn'           => 0,
            'lost'            => 0,
            'goals_for'       => 0,
            'goals_against'   => 0,
            'goal_difference' => 0,
            'points'          => 0,
        ];
    }
}
