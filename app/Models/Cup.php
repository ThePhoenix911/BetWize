<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Cup extends Model
{
    protected $fillable = ['name', 'country', 'logo_url', 'api_id'];

    /**
     * Returns the Fixtures for this Cup.
     * STATUS: Correct.
     * WHY: Using MorphMany allows the 'fixtures' table to point back to this Cup
     * using 'competition_id' and 'competition_type'.
     */
    public function fixtures(): MorphMany
    {
        return $this->morphMany(Fixture::class, 'competition');
    }

    /**
     * Returns the Clubs participating in this Cup.
     * STATUS: Updated.
     * WHY: We use belongsToMany because a Club can play in multiple Cups
     * (MTN8, Nedbank, etc.), and a Cup obviously has many Clubs.
     */
    public function clubs(): BelongsToMany
    {
        // This expects a 'club_cup' bridge table.
        return $this->belongsToMany(Club::class);
    }

}


