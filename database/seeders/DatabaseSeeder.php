<?php
// database/seeders/DatabaseSeeder.php

// WHAT: Seeds the database with test data so you can develop and test without real API data.
// HOW: Run with: php artisan db:seed    (or as part of: php artisan migrate:fresh --seed)
// WHY: Your fridge analogy was perfect.
//      The seeder is a supplier who gives you sample data to test your fridge.
//      You need to see data in the browser to know your Livewire components are working.
//      Real API data requires API keys, network access, and waiting for imports.
//      Seeder data is instant, offline, and predictable for testing.
// IMPORTANT: Seeder data is for DEVELOPMENT ONLY.
//      Never run seeders on production (they'd overwrite real data).

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Cup;
use App\Models\Fixture;
use App\Models\League;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ======== 1. TEST USER ========
        User::factory()->create([
            'name'  => 'BetWize Tester',
            'email' => 'test@betwize.com',
        ]);
        // WHAT: Creates a user you can log in with during development.
        // HOW: factory()->create() uses the UserFactory (in database/factories/)
        //      to generate a realistic user. We override name and email.
        //      The factory automatically generates a hashed password.
        //      The default factory password is 'password'.
        // WHY: You need a user to test authentication and the follow system.

        // ======== 2. TEST LEAGUE ========
        $league = League::create([
            'name'       => 'Betway Premiership',
            'country'    => 'South Africa',
            'short_code' => 'PSL',
            'api_id'     => 288,
            // api_id=288 is the REAL PSL ID in API-Football.
            // Using the real ID means when you later run ImportFixtures,
            // the data slots directly into this league without any manual mapping.
        ]);

        // ======== 3. TEST CLUBS ========
        $pirates = Club::create([
            'name'       => 'Orlando Pirates',
            'short_code' => 'ORL',
            'logo_url'   => null,
            'api_id'     => 1000,
        ]);
        // Attach to the league for the current season via the pivot table
        $pirates->leagues()->attach($league->id, ['season' => now()->year]);

        $chiefs = Club::create([
            'name'       => 'Kaizer Chiefs',
            'short_code' => 'KZC',
            'logo_url'   => null,
            'api_id'     => 1001,
        ]);
        $chiefs->leagues()->attach($league->id, ['season' => now()->year]);

        $sundowns = Club::create([
            'name'       => 'Mamelodi Sundowns',
            'short_code' => 'MSD',
            'logo_url'   => null,
            'api_id'     => 1002,
        ]);
        $sundowns->leagues()->attach($league->id, ['season' => now()->year]);

        // ======== 4. TEST FIXTURES ========
        // Create some finished fixtures so StandingsCalculator has data to work with.

        // Fixture 1: Pirates beat Chiefs 2-1 (FT)
        Fixture::create([
            'competition_id'   => $league->id,
            'competition_type' => League::class,
            'home_team_id'     => $pirates->id,
            'away_team_id'     => $chiefs->id,
            'match_at'         => now()->subDays(14),
            // subDays(14) = 14 days ago. Shows a past match.
            'status'           => 'FT',
            'home_score'       => 2,
            'away_score'       => 1,
            'api_id'           => 50001,
        ]);

        // Fixture 2: Sundowns beat Chiefs 3-0 (FT)
        Fixture::create([
            'competition_id'   => $league->id,
            'competition_type' => League::class,
            'home_team_id'     => $sundowns->id,
            'away_team_id'     => $chiefs->id,
            'match_at'         => now()->subDays(7),
            'status'           => 'FT',
            'home_score'       => 3,
            'away_score'       => 0,
            'api_id'           => 50002,
        ]);

        // Fixture 3: Pirates vs Sundowns (upcoming — today)
        Fixture::create([
            'competition_id'   => $league->id,
            'competition_type' => League::class,
            'home_team_id'     => $pirates->id,
            'away_team_id'     => $sundowns->id,
            'match_at'         => now()->setTime(15, 30),
            // setTime(15, 30) = today at 15:30. Shows up in FixtureList.
            'status'           => 'NS',
            'home_score'       => null,
            'away_score'       => null,
            'api_id'           => 50003,
        ]);
    }
}
