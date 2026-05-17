<?php
// app/Livewire/FixtureList.php

namespace App\Livewire;

use App\Models\Fixture;
use App\Models\League;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class FixtureList extends Component
// WHAT: Displays today's fixtures across all leagues, with live score polling.
// KEY FEATURE: wire:poll in the blade template refreshes this component every 60 seconds.
//              Scores update automatically. Zero JavaScript written by you.
{
    public ?int $leagueFilter = null;
    // WHAT: Optional filter — null means "show all leagues".
    //       Setting this to a league ID filters to just that league.
    // HOW: '?int' means nullable integer. Can be null OR an integer.
    // WHY: public makes it reactive. When the user selects a league from a dropdown,
    //      Livewire updates this property and the fixtures list re-renders automatically.

    #[Computed]
    public function fixtures(): Collection
    {
        return Fixture::with(['homeTeam', 'awayTeam', 'competition'])
            // WHAT: Eager load home team, away team, AND the competition (League or Cup).
            // WHY: We'll display competition name in the fixture row.
            //      Eager loading prevents N+1 queries.

            ->whereDate('match_at', today())
            // WHAT: Filter to TODAY's fixtures only.
            // HOW: whereDate() compares only the DATE portion of a DATETIME column.
            //      today() returns a Carbon date for today.
            //      This becomes: WHERE DATE(match_at) = '2025-03-29'
            // WHY: The fixture list shows what's happening TODAY.
            //      Without this, every fixture in the database would show.

            ->when($this->leagueFilter, function ($query, $leagueId) {
                $query->where('competition_id', $leagueId)
                    ->where('competition_type', League::class);
            })
            // WHAT: Conditionally adds a WHERE clause if leagueFilter is set.
            // HOW: when($condition, $callback) only applies the callback if $condition is truthy.
            //      null = falsy → no filter applied (show all leagues)
            //      3    = truthy → filter applied (show only league 3)
            // WHY: Cleaner than an if/else. The query builds itself based on state.
            // VANILLA EQUIVALENT:
            //      if ($this->leagueFilter) {
            //          $query->where('competition_id', $this->leagueFilter);
            //      }

            ->orderBy('match_at')
            // WHAT: Sort fixtures by kick-off time (earliest first).
            ->get();
    }

    #[Computed]
    public function leagues(): \Illuminate\Support\Collection
    {
        // Fetched here in the component, not in the blade
        // WHY: The component is a PHP class with proper namespace imports.
        //      The blade template just receives and displays data.
        //      Keeping database calls in the component keeps blade clean.
        return \App\Models\League::all();
    }

    public function render(): View
    {
        return view('livewire.fixture-list');
    }
}
