<?php
// app/Livewire/LeagueTable.php

namespace App\Livewire;

use App\Models\League;
use App\Support\StandingsCalculator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LeagueTable extends Component
// WHAT: A Livewire component that displays a football league standings table.
// HOW: Extends Component, which gives it Livewire's reactive powers.
// THINK OF IT AS: A self-contained web widget.
//   The class handles data. The blade template handles display.
//   When data changes, the template automatically re-renders.
{
    // WHAT: A public property holding the currently selected league.
    // HOW: 'public' properties in Livewire are REACTIVE.
    //      If this property changes, Livewire re-renders the blade template automatically.
    //      The blade can also read this directly: {{ $league->name }}
    // WHY: public vs private matters in Livewire.
    //      Public = synced with the frontend, re-renders on change.
    //      Private/protected = server-side only, not reactive.
    public League $league;

    public int $season;
    // WHAT: The season year being displayed (e.g. 2025).
    // WHY: Making this public means you could later add a year selector
    //      dropdown that updates this, and the standings would refresh automatically.

    public function mount(): void
        // WHAT: Runs ONCE when the component first loads on the page.
        // HOW: Like a constructor specifically for the component's initial state.
        // WHY: 'mount' vs '__construct':
        //      __construct runs every time PHP instantiates the class (including during AJAX).
        //      mount() runs ONLY on the initial page load.
        //      Initial data setup belongs in mount() to avoid re-running on every update.
        // VANILLA EQUIVALENT: Like an __init() function that only runs on first page load.
    {
        $this->league = League::first();
        // WHAT: Loads the first league from the database as the default.
        // WHY: The component needs a league to display. Later you'll add a selector
        //      so the user can choose which league to view.

        $this->season = now()->year;
        // WHAT: Sets the season to the current year.
        // HOW: now() returns a Carbon datetime object. ->year extracts the year integer.
        // WHY: Sensible default — users want current season standings by default.
    }

    #[Computed]
    public function standings(): Collection
        // WHAT: A computed property that calculates and returns the standings table.
        // HOW: #[Computed] is a PHP attribute (like a decorator/annotation).
        //      It tells Livewire: "cache this result for the current render cycle."
        //      If the template calls $this->standings twice, the calculation only runs once.
        // WHY NOT JUST A REGULAR METHOD:
        //      A regular public property would need to be set in mount() and updated manually.
        //      A computed property recalculates automatically whenever $league or $season changes.
        //      Perfect for derived data (data calculated FROM other data).
        // VANILLA EQUIVALENT: Like a getter method that caches its result.
    {
        return app(StandingsCalculator::class)->calculate($this->league, $this->season);
        // WHAT: app() retrieves an instance from Laravel's Service Container.
        // HOW: Same as 'new StandingsCalculator()' but goes through Laravel's container.
        //      This means if StandingsCalculator ever needs dependencies injected,
        //      the container handles it automatically.
        // WHY: Consistent with how Laravel resolves dependencies everywhere else.
    }

    public function render(): View
        // WHAT: Returns the blade view this component uses for its HTML.
        // HOW: Called automatically by Livewire on every render (initial load + every update).
        // WHY: Livewire calls render() → gets the view → renders it to HTML → sends to browser.
        //      The view has access to all public properties of this class automatically.
    {
        return view('livewire.league-table');
        // Maps to: resources/views/livewire/league-table.blade.php
    }
}
