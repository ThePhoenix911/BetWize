<?php
// app/Jobs/ImportTeams.php

// WHAT: A background job that imports club (team) data from API-Football into our database.
// WHEN IT RUNS: On a schedule (monthly — teams don't change often) or manually via tinker.
// DEPENDENCY: Must run BEFORE ImportFixtures.
//             ImportFixtures skips fixtures if either club doesn't exist in our database.
//             This job is what puts the clubs IN the database.
// THINK OF IT AS: The "stock the pantry" job. Run once. Kitchen is ready.

namespace App\Jobs;

use App\Models\Club;
use App\Models\League;
use App\Services\FootballApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportTeams implements ShouldQueue
// WHAT: 'implements ShouldQueue' is a CONTRACT with Laravel.
// HOW: Interfaces in PHP define methods a class MUST implement.
//      ShouldQueue tells Laravel: "push this to the queue, run it in background."
//      Without ShouldQueue, dispatch() would run the job immediately (synchronously).
// VANILLA EQUIVALENT: Like putting a task on a TODO list instead of doing it right now.
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    // WHAT: Four traits that inject queue abilities into this class.
    // Dispatchable  → adds the static dispatch() method so you can call ImportTeams::dispatch()
    // Queueable     → allows this job to be assigned to specific queues
    // InteractsWithQueue → lets the job interact with the queue (retry, fail, release back)
    // SerializesModels → CRITICAL: when the job is stored in the queue (as text/JSON),
    //                   Eloquent models can't be stored as-is.
    //                   SerializesModels saves just the model's ID.
    //                   When the job runs, it fetches the fresh model from the database.
    //                   WHY: The League object might have changed between dispatch and execution.
    //                   Fresh fetch = current data. Serialized object = potentially stale data.

    public int $tries = 3;
    // WHAT: How many times Laravel retries this job if it fails.
    // WHY: API calls fail temporarily — server hiccup, rate limit, brief outage.
    //      Retrying 3 times gives transient failures a chance to resolve.
    //      After 3 failures, the job is marked as permanently failed and logged.

    public int $timeout = 120;
    // WHAT: Maximum seconds a single attempt can run before being force-killed.
    // WHY: If API-Football hangs and never responds, we don't want this job
    //      blocking the queue worker forever. 120 seconds = 2 minutes is generous.

    public function __construct(
        private readonly League $league,
        private readonly int $season,
    )
        // WHAT: The job's "briefing" — information it needs when dispatched.
        // HOW: Called automatically when you do ImportTeams::dispatch($league, 2025).
        //      'private readonly' means these values are:
        //      - private: accessible only within this class
        //      - readonly: cannot be changed after the constructor sets them
        // WHY: We pass the League in (not just an ID) so SerializesModels can handle it.
        //      We pass the season so this job works for any year, not just 2025.
        // VANILLA EQUIVALENT: Like a function that accepts parameters when called.
    {}

    public function handle(FootballApiService $api): void
        // WHAT: The method Laravel calls when the queue worker picks up this job.
        // HOW: FootballApiService $api is METHOD INJECTION.
        //      Laravel's Service Container sees the type hint, builds the FootballApiService
        //      (with all its constructor logic), and passes it in automatically.
        //      You never call 'new FootballApiService()' yourself.
        // WHY METHOD INJECTION vs CONSTRUCTOR INJECTION:
        //      Constructor = data the job CARRIES (league, season — needed at dispatch time)
        //      Method      = services the job USES (FootballApiService — needed at execution time)
        //      Services are injected at execution time so they're always fresh.
        // VANILLA EQUIVALENT:
        //      public function handle() {
        //          $api = new FootballApiService(); // you built it manually
        //      }
        //      Laravel builds and injects it for you. Same result, less work, more testable.
    {
        Log::info("Starting teams import", [
            'league' => $this->league->name,
            'season' => $this->season,
        ]);
        // WHY LOG: Background jobs have no screen to print to. The log file is your window
        //          into what happened. storage/logs/laravel.log captures everything.

        $teamsData = $api->getTeams(
            leagueId: $this->league->api_id,
            season:   $this->season
        );
        // WHAT: Calls the service to fetch team data from API-Football.
        // HOW: Named arguments (leagueId:, season:) make the call self-documenting.
        //      You know what each value is without checking the method signature.
        // WHY: If getTeams() fails internally, it returns [] and logs an error.
        //      The foreach below simply doesn't run on an empty array.

        $importedCount = 0;

        foreach ($teamsData as $data) {
            // WHAT: Loops through each team object returned by the API.
            // WHY: API-Football returns ALL teams in one array.
            //      We process and save them one by one.

            $team = $data['team'];
            // WHAT: Extracts the 'team' sub-array from the current iteration.
            // WHY: API-Football nests team details inside a 'team' key:
            //      { "team": { "id": 1001, "name": "Orlando Pirates", "code": "ORL" }, ... }
            //      We need to go one level deeper to get id, name, code.

            $club = Club::updateOrCreate(
            // WHAT: Either UPDATE an existing record or CREATE a new one.
            // HOW: Takes two arrays:
            //      1st array = HOW TO FIND an existing record (the WHERE clause)
            //      2nd array = WHAT TO SET on that record (INSERT or UPDATE values)
            // EXAMPLE: Club with api_id=1001 already exists?
            //          → UPDATE its name, short_code, logo_url with new values.
            //          Club with api_id=1001 doesn't exist yet?
            //          → INSERT a new row with ALL the provided values.
            // WHY: This job runs multiple times. Without updateOrCreate, we'd get
            //      duplicate clubs on every run. With it, we safely sync each time.
            // VANILLA EQUIVALENT:
            //      $existing = "SELECT * FROM clubs WHERE api_id = ?";
            //      if ($existing) { UPDATE ... } else { INSERT ... }
                ['api_id' => $team['id']],      // WHERE api_id = this team's ID
                [
                    'name'       => $team['name'],
                    'short_code' => $team['code'] ?? null,
                    // '?? null' = NULL COALESCING OPERATOR.
                    // If $team['code'] doesn't exist or is null, use null.
                    // WHY: Some teams don't have a code in API-Football.
                    //      Without ??, this would throw an undefined index error.
                    'logo_url'   => $team['logo'] ?? null,
                ]
            );

            $alreadyAttached = $club->leagues()
                ->where('league_id', $this->league->id)
                ->wherePivot('season', $this->season)
                ->exists();
            // WHAT: Checks if this club-league-season combination already exists in the pivot table.
            // HOW: Queries the club_league pivot table for this specific club, league, and season.
            //      exists() returns true or false — doesn't load the actual record, just checks.
            // WHY: The club_league table has a UNIQUE constraint on (club_id, league_id, season).
            //      If we try to attach them twice, MySQL throws a constraint violation error.
            //      Checking first prevents the crash.

            if (!$alreadyAttached) {
                $club->leagues()->attach($this->league->id, ['season' => $this->season]);
                // WHAT: Inserts a new row into the club_league pivot table.
                // HOW: attach() is the BelongsToMany method for inserting pivot records.
                //      First argument  = the related model's ID (the league's ID)
                //      Second argument = extra column values to save on the pivot row
                // RESULT: A new row in club_league: { club_id: X, league_id: Y, season: 2025 }
            }

            $importedCount++;
        }

        Log::info("Teams import complete", [
            'league'   => $this->league->name,
            'season'   => $this->season,
            'imported' => $importedCount,
        ]);
    }

    public function failed(\Throwable $exception): void
        // WHAT: Called automatically by Laravel if the job fails ALL retries.
        // WHY: Final safety net. After 3 failed attempts, this logs the cause clearly.
        //      Without this, you'd have to dig through queue failure tables to find out why.
    {
        Log::error("ImportTeams job failed permanently", [
            'league'  => $this->league->name,
            'season'  => $this->season,
            'message' => $exception->getMessage(),
        ]);
    }
}
