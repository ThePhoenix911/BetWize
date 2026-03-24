<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Fixture;
use App\Models\League;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // 1. Create a user for testing - to login easily
        User::factory()->create([
            'name' => 'BetWize Tester',
            'email' => 'test@betwize.com',
        ]);


        // 2. Create the Parent League
        $league = League::create([
            'name' => 'Betway Premiership',
            'country' => 'South Africa',
            'short_code' => 'PSL',
            'api_id' => 288 // Example API ID
        ]);

        // 3 Create the Clubs (The big three)
        $pirates = Club::create([
            'league_id' => $league->id,
            'name' => 'Orlando Pirates',
            'short_code' => 'ORL',
            'api_id' => 1001
        ]);

        $chiefs = Club::create([
            'league_id' => $league->id,
            'name' => 'Kaizer Chiefs',
            'short_code' => 'KZC',
            'api_id' => 1002
        ]);

        $sundowns = Club::create([
            'league_id' => $league->id,
            'name' => 'Mamelodi Sundowns',
            'short_code' => 'MSD',
            'api_id' => 1003
        ]);


        // 4. Create a 'Bridge' (Fixture)  - The Soweto Derby
        Fixture::create([
            'competition_id' => $league->id,
            'competition_type' => League::class,    // Telling Laravel this is League match
            'home_team_id' => $pirates->id,
            'away_team_id' => $chiefs->id,
            'match_at' => now()->addDays(2), // Match happening in 2 days
            'status' => 'NS',   //Not Started
            'api_id' => 50001
        ]);
    }
}
