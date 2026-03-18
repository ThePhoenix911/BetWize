<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Fixture extends Model
{
    protected $fillable = ['competition_id', 'competition_type', 'home_team_id',
        'away_team_id', 'match_at', 'status', 'home_score', 'away_score', 'api_id'];


    // Casts the 'match_at' column to a DateTime object
    // This allows us to easily work with the date and time of the match
    // It's a way of saying "when you retrieve this column, convert it to a DateTime object"
    // This is a security measure to prevent unwanted data from being inserted into the database
    // It's a way of saying "only allow these columns to be mass-assigned"
    protected $casts = ['match_at' => 'datetime'];


    /********** Relationships - who it shares data with **********/

    // Returns the League or Cup that this Fixture belongs to
    public function competition(): MorphTo
    {
        return $this->morphTo();
    }


    // Returns the Club that is the home team for this Fixture
    public function homeTeam(): BelongsTo
    {
        // A Fixture can have one Club as the home team
        return $this->belongsTo(Club::class, 'home_team_id');

    }

    // Returns the Club that is the away team for this Fixture
    public function awayTeam(): BelongsTo
    {
        // A Fixture can have one Club as the away team
        return $this->belongsTo(Club::class, 'away_team_id');

    }
}
