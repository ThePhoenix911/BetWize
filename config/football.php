<?php
// config/football.php

// WHAT: Returns a PHP array of football API settings.
// HOW: Laravel automatically loads every file in the config/ folder.
//      You access values with config('football.key') — the filename is the first part,
//      the array key is the second part.
// WHY: Centralised config. Change the API URL once here and every class that
//      calls config('football.base_url') gets the new value automatically.
// VANILLA EQUIVALENT: Like a settings.php you include everywhere, but cached by Laravel.

return [

    // WHAT: Your API-Football authentication key.
    // HOW: env('API_FOOTBALL_KEY') reads the value from your .env file.
    // WHY: We read from config() in classes, not env() directly.
    //      env() is slow (reads a file every time). config() is cached in memory.
    //      Rule: env() belongs only in config files. config() belongs everywhere else.
    'key' => env('API_FOOTBALL_KEY'),

    // WHAT: The base URL for every API-Football HTTP request.
    // HOW: The second argument to env() is a default value.
    //      If API_FOOTBALL_BASE_URL is missing from .env, this string is used instead.
    // WHY: Prevents the app from crashing if someone forgets to add the URL to .env.
    'base_url' => env('API_FOOTBALL_BASE_URL', 'https://v3.football.api-sports.io'),
];


