<?php

namespace App\Livewire;

use App\Models\League;
use Livewire\Component;

class LeagueTable extends Component
{
    // The "Season Ticket" - this persists between clicks
    public League $league;

    public function mount()
    {
        // We fetch it once when the component is born first
        $this->league = League::with('clubs')->first();
    }

    public function render()
    {
        // Render is clean; it just shows what the component "owns"
        return view('livewire.league-table');
    }
}
