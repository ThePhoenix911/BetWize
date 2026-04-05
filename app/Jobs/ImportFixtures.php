<?php
// app/Jobs/ImportFixtures.php

// WHAT: Fetches match data from API-Football and syncs it to your fixtures table.
// WHEN IT RUNS: Every hour via the Scheduler.
// DEPENDENCY: Requires clubs to already exist in the database (run ImportTeams first).
// THINK OF IT AS: The "restock the fridge hourly" job.

namespace App\Jobs;

use App\Models\Club;
use App\Models\League;
use App\Models\Fixture;
use App\Services\FootballApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportFixtures implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        private readonly League $league,
        private readonly int $season,
    ) {}

    public function handle(FootballApiService $api): void
    {
        Log::info("Starting fixture import", [
            'league' => $this->league->name,
            'season' => $this->season,
        ]);

        $fixtures = $api->getFixtures(
            leagueId: $this->league->api_id,
            season:   $this->season,
        );

        $newCount     = 0;
        $updatedCount = 0;

        foreach ($fixtures as $fixtureData) {

            // WHAT: Extract the three data sections from each fixture object.
            // WHY: API-Football structures each fixture as three nested sections.
            //      fixture = match info (id, date, status)
            //      teams   = home and away team info
            //      goals   = current scores
            $fixture = $fixtureData['fixture'];
            $teams   = $fixtureData['teams'];
            $goals   = $fixtureData['goals'];

            // WHAT: Find the matching clubs in OUR database using their api_id.
            // HOW: Club::where('api_id', X)->first() runs:
            //      SELECT * FROM clubs WHERE api_id = X LIMIT 1
            // WHY: We look up by api_id, NOT by name.
            //      API team names can vary: "Man Utd" vs "Manchester United" vs "Man. United"
            //      api_id is always the same integer, regardless of name formatting.
            $homeClub = Club::where('api_id', $teams['home']['id'])->first();
            $awayClub = Club::where('api_id', $teams['away']['id'])->first();

            if (!$homeClub || !$awayClub) {
                // WHAT: Skip this fixture if either club isn't in our database.
                // WHY: We can't save a fixture with a foreign key pointing to a
                //      non-existent club — the database would reject it.
                //      This happens when ImportTeams hasn't been run for a league yet.
                Log::warning('Skipping fixture — club not found', [
                    'home_api_id'    => $teams['home']['id'],
                    'away_api_id'    => $teams['away']['id'],
                    'fixture_api_id' => $fixture['id'],
                ]);
                continue;
                // 'continue' skips the rest of this loop iteration
                // and moves to the next fixture in the array.
            }

            $result = Fixture::updateOrCreate(
                ['api_id' => $fixture['id']],
                // FIND BY: api_id — our anchor to API-Football's world.

                [
                    'competition_id'   => $this->league->id,
                    'competition_type' => League::class,
                    // WHAT: League::class returns the string 'App\Models\League'.
                    // WHY: That's exactly what Laravel's polymorphic system expects
                    //      in the competition_type column.
                    //      Using League::class instead of a hardcoded string means
                    //      if the namespace ever changes, this updates automatically.

                    'home_team_id' => $homeClub->id,
                    'away_team_id' => $awayClub->id,

                    'match_at' => $fixture['date'],
                    // API-Football sends: "2025-03-29T15:30:00+00:00" (ISO 8601)
                    // Laravel's 'datetime' cast handles this format automatically.

                    'status'     => $fixture['status']['short'],
                    'home_score' => $goals['home'],
                    // null before match, integer during/after. Database column is nullable.
                    'away_score' => $goals['away'],
                ]
            );

            // WHAT: wasRecentlyCreated is a built-in Eloquent property.
            // HOW: updateOrCreate sets this flag after running.
            //      true  = a new row was INSERTED (first time we've seen this fixture)
            //      false = an existing row was UPDATED (we've seen this fixture before)
            // WHY: Helps you understand your import logs.
            //      First run: new=380, updated=0 ✓ (imported everything fresh)
            //      Second run: new=0, updated=380 ✓ (updated existing records)
            //      Third run: new=5, updated=375 ✓ (5 new fixtures were added to the schedule)
            //      Second run: new=380, updated=0 ✗ (something is wrong with the upsert)
            if ($result->wasRecentlyCreated) {
                $newCount++;
            } else {
                $updatedCount++;
            }
        }

        Log::info("Fixture import complete", [
            'league'  => $this->league->name,
            'season'  => $this->season,
            'new'     => $newCount,
            'updated' => $updatedCount,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ImportFixtures job failed permanently", [
            'league'  => $this->league->name,
            'season'  => $this->season,
            'message' => $exception->getMessage(),
        ]);
    }
}
