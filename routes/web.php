<?php
// routes/web.php

// WHAT: Defines which URL paths map to which responses.
// WHY: Laravel's router is the front door of your app.
//      Every HTTP request first hits the router, which decides where to send it.
// VANILLA EQUIVALENT: Like a big if/elseif block based on $_SERVER['REQUEST_URI'],
//      or an .htaccess RewriteRule that maps URLs to PHP files.
//      But cleaner, chainable, and with middleware built in.

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // WHAT: When someone visits the root URL (/), run this closure.
    // HOW: get() matches GET requests. The second argument is what to do.
    //      Here it returns a view.
    // WHY: The home page shows today's fixtures and a league table.
    return view('home');
    // WHAT: Returns the compiled HTML of resources/views/home.blade.php
    // WHY: Laravel finds the blade file, compiles it to PHP, runs it, returns HTML.
});

Route::get('/leagues', function () {
    // WHAT: List of all leagues in the system.
    $leagues = \App\Models\League::all();
    return view('leagues.index', ['leagues' => $leagues]);
    // WHAT: Passes $leagues to the blade view.
    // HOW: Second argument to view() is an array of data.
    //      Keys become variables in the blade: {{ $leagues }}
});

Route::get('/leagues/{league}', function (\App\Models\League $league) {
    // WHAT: Shows a specific league's standings and fixtures.
    // HOW: {league} is a ROUTE PARAMETER — a variable in the URL.
    //      Type-hinting \App\Models\League tells Laravel to use ROUTE MODEL BINDING.
    //      Route Model Binding automatically runs League::find($id) for you.
    //      If the league doesn't exist, Laravel returns a 404 automatically.
    // VANILLA EQUIVALENT:
    //      $id = $_GET['id'];
    //      $league = League::find($id);
    //      if (!$league) { http_response_code(404); die(); }
    return view('leagues.show', ['league' => $league]);
})->name('leagues.show');
// WHAT: ->name() gives this route a name.
// WHY: Instead of hardcoding URLs in your blade templates as href="/leagues/3",
//      you can use route('leagues.show', $league) and Laravel generates the URL.
//      If you ever change the URL from /leagues to /competitions, you change it
//      ONCE in routes/web.php and every link throughout the app updates automatically.
