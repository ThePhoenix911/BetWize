<?php
// routes/console.php

// WHAT: Defines scheduled tasks (the "alarm clock" layer of BetWize).
// HOW: Laravel's scheduler runs every minute (triggered by a single cron entry).
//      You define WHEN each job should run. Laravel handles the rest.
// WHY: Without a scheduler, someone would have to manually trigger imports.
//      The scheduler makes your app self-maintaining.
// VANILLA EQUIVALENT: Like setting up cron entries directly, but in PHP
//      where you can use human-readable schedules like ->hourly() or ->daily().

use App\Jobs\ImportTeams;
use App\Jobs\ImportFixtures;
use App\Models\League;
use Illuminate\Support\Facades\Schedule;

// Import teams MONTHLY — team rosters change rarely.
// This ensures the clubs table is always populated before fixture imports.
Schedule::call(function () {
    League::all()->each(function ($league) {
        ImportTeams::dispatch($league, now()->year);
        // WHAT: Dispatches an ImportTeams job for EACH league in your database.
        // HOW: League::all() returns every league.
        //      each() loops through them like foreach.
        //      dispatch() queues the job (or runs it synchronously if QUEUE_CONNECTION=sync in .env).
        // WHY: Runs for every league automatically — you don't have to add
        //      a new schedule entry every time you add a league to the database.
    });
})->monthly();

// Import fixtures HOURLY — match statuses and scores change frequently.
Schedule::call(function () {
    League::all()->each(function ($league) {
        ImportFixtures::dispatch($league, now()->year);
    });
})->hourly();

// TO ACTIVATE THE SCHEDULER:
// Development: php artisan schedule:run   (run manually to test)
// Production: Add this ONE cron entry to your server's crontab:
// * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
// This runs every minute. Laravel checks the schedule and decides what to run.

