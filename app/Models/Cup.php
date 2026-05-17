<?php
// app/Models/Cup.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Cup extends Model
{
    protected $fillable = ['name', 'country', 'logo_url', 'api_id'];

    public function fixtures(): MorphMany
        // WHAT: All fixtures that belong to this cup competition.
        // HOW: Identical pattern to League's fixtures() method.
        //      'competition' matches the morphs('competition') relationship name.
        // WHY: Enables $cup->fixtures to retrieve all CAF CL matches, for example.
    {
        return $this->morphMany(Fixture::class, 'competition');
    }

    public function clubs(): BelongsToMany
        // WHAT: All clubs participating in this cup.
        // HOW: BelongsToMany expects a 'club_cup' pivot table (alphabetical order).
        //      You'll need to create this migration when you build the cups feature.
        // WHY: A club can enter multiple cups. A cup has multiple clubs.
        //      Many-to-many = pivot table.
    {
        return $this->belongsToMany(Club::class);
    }
}
