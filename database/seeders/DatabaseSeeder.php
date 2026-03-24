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
            'name' => 'Orlando Pirates',
            'short_code' => 'ORL',
            'api_id' => 1001
        ]);
        // attach() is the BelongsToMany method for inserting into the pivot table
        // first argument = the related model's id
        // second argument = the extra pivot columns you want to set (season in this case)
        $pirates->leagues()->attach($league->id, ['season' => 2025]);


        $chiefs = Club::create([
            'name' => 'Kaizer Chiefs',
            'short_code' => 'KZC',
            'api_id' => 1002
        ]);
        $chiefs->leagues()->attach($league->id, ['season' => 2025]);

        $sundowns = Club::create([
            'name' => 'Mamelodi Sundowns',
            'short_code' => 'MSD',
            'api_id' => 1003
        ]);
        $sundowns->leagues()->attach($league->id, ['season' => 2025]);





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
