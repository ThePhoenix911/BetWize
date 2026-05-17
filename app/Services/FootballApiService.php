<?php
// app/Services/FootballApiService.php

// WHAT: A plain PHP class that handles ALL communication with API-Football.
// WHY IT EXISTS AS A SEPARATE CLASS:
//   You could put the API logic directly inside the Import Jobs.
//   But then if you ever need to call the API from a different place
//   (like a one-off artisan command, or a future feature), you'd copy-paste the code.
//   Copy-pasted code = bugs in two places when something changes.
//   One service class = fix it once, every caller benefits.
// VANILLA EQUIVALENT: Like a dedicated APIHelper.php you include wherever needed.
// SINGLE RESPONSIBILITY: This class does ONE thing — make HTTP requests to API-Football.
//   It does NOT save to the database. It does NOT calculate standings.
//   It ONLY fetches raw data and returns it as PHP arrays.

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballApiService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
        // WHAT: Runs automatically when you create an instance of this class.
        // HOW: Reads from config() which reads from .env via config/football.php.
        // WHY: Stored as private properties so every method can access them
        //      without reading config() again on each call.
        // NOTE: config() is cached in memory by Laravel. Reading it 100 times
        //       is the same cost as reading it once. Still cleaner to read once here.
    {
        $this->apiKey  = config('football.key');
        $this->baseUrl = config('football.base_url');
    }

    private function request(): PendingRequest
        // WHAT: Builds and returns a pre-configured HTTP client ready to make requests.
        // HOW: Http::withHeaders() attaches headers to every request made with this client.
        //      baseUrl() sets a URL prefix so methods only need to add the endpoint path.
        // WHY: Private because only THIS class uses it. Not for external callers.
        //      DRY principle — instead of repeating Http::withHeaders() in every method,
        //      we build the base once and reuse it.
        // VANILLA EQUIVALENT:
        //      Like setting up a cURL handle with default options once,
        //      then reusing it for each specific request.
    {
        return Http::withHeaders([
            'x-apisports-key' => $this->apiKey,
            // API-Football requires this header on every request for authentication.
            // Without it, every request returns a 401 Unauthorised error.
        ])->baseUrl($this->baseUrl);
    }

    public function getFixtures(int $leagueId, int $season): array
        // WHAT: Fetches all fixtures for a specific league and season.
        // HOW: Makes a GET request to /fixtures with league and season as query params.
        //      This becomes: GET https://v3.football.api-sports.io/fixtures?league=288&season=2025
        // RETURNS: Array of fixture data or empty array on failure.
        // WHY: Used by ImportFixtures job to sync match data hourly.
    {
        try {
            $response = $this->request()->get('/fixtures', [
                'league' => $leagueId,
                'season' => $season,
            ]);

            // WHAT: Extracts the 'response' key from the returned JSON.
            // HOW: json('response', []) navigates the JSON structure.
            //      API-Football wraps all data inside a 'response' key:
            //      { "response": [ {...fixture1...}, {...fixture2...} ] }
            //      The second argument [] is the default if 'response' doesn't exist.
            // WHY: Defensive programming. If API-Football changes their response structure,
            //      or returns an error object with no 'response' key,
            //      we return an empty array instead of crashing.
            return $response->json('response', []);

        } catch (\Exception $e) {
            // WHAT: Catches any exception (network timeout, DNS failure, etc.)
            // WHY: Background jobs must NEVER crash the queue worker.
            //      If the internet goes down at 3am, the job should log the error
            //      and return an empty array, not kill the entire queue process.
            Log::error('API-Football getFixtures failed', [
                'league'  => $leagueId,
                'season'  => $season,
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getTeams(int $leagueId, int $season): array
        // WHAT: Fetches all teams (clubs) registered in a specific league and season.
        // WHY: Run ONCE (or occasionally) to populate the clubs table.
        //      ImportTeams job calls this, then saves each team to your database.
    {
        try {
            $response = $this->request()->get('/teams', [
                'league' => $leagueId,
                'season' => $season,
            ]);
            return $response->json('response', []);
        } catch (\Exception $e) {
            Log::error('API-Football getTeams failed', [
                'league'  => $leagueId,
                'season'  => $season,
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getStandings(int $leagueId, int $season): array
        // WHAT: Fetches pre-calculated standings from API-Football.
        // WHY: You have two options for standings:
        //      1. Calculate from your own fixtures table (StandingsCalculator)
        //      2. Fetch directly from the API (this method)
        //      Option 1 is better for offline/cached scenarios.
        //      Option 2 is useful as a sanity check or fallback.
    {
        try {
            $response = $this->request()->get('/standings', [
                'league' => $leagueId,
                'season' => $season,
            ]);
            // NOTE: Standings are nested deeper than fixtures.
            // The path 'response.0.league.standings.0' navigates to the actual table array.
            return $response->json('response.0.league.standings.0', []);
        } catch (\Exception $e) {
            Log::error('API-Football getStandings failed', [
                'league'  => $leagueId,
                'season'  => $season,
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
