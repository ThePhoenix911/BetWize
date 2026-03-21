<?php

namespace App\Livewire;

use App\Models\League;
use Livewire\Component;

class LeagueTable extends Component
{
    public function render()
    {
        $league = League::with('clubs')->first();

        return view('livewire.league-table', ['league' => $league]);
    }
}
