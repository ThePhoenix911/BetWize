<?php
// app/Models/Fixture.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Fixture extends Model
{
    protected $fillable = [
        'competition_id', 'competition_type',
        'home_team_id', 'away_team_id',
        'match_at', 'status',
        'home_score', 'away_score',
        'api_id'
    ];

    // WHAT: Tells Laravel how to cast certain columns when reading them.
    // HOW: When you access $fixture->match_at, Laravel automatically converts
    //      the raw datetime string from the database into a Carbon object.
    //      Carbon is a PHP date library — it gives you methods like:
    //      $fixture->match_at->format('D d M') → "Sat 29 Mar"
    //      $fixture->match_at->diffForHumans()  → "in 2 days"
    // WHY: Dates stored in MySQL are plain strings like "2025-03-29 15:30:00".
    //      Without casting, you'd have to convert them manually every time.
    // VANILLA EQUIVALENT: Like running strtotime() on every date you pull from MySQL.
    protected $casts = [
        'match_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function competition(): MorphTo
        // WHAT: Returns either the League OR the Cup that this fixture belongs to.
        // HOW: MorphTo is the "many side" of a polymorphic relationship.
        //      It reads competition_type to know which model to load,
        //      then uses competition_id to find the specific record.
        //      If competition_type = 'App\Models\League' and competition_id = 3,
        //      it returns League::find(3).
        // WHY: One method that works for both League fixtures AND Cup fixtures.
        //      No if/else. No separate league_id and cup_id columns.
        // VANILLA EQUIVALENT:
        //      if ($this->competition_type === 'App\Models\League') {
        //          return League::find($this->competition_id);
        //      } else {
        //          return Cup::find($this->competition_id);
        //      }
    {
        return $this->morphTo();
        // 'morphTo()' with no arguments uses the column names from the migration:
        // competition_id and competition_type (derived from morphs('competition'))
    }

    public function homeTeam(): BelongsTo
        // WHAT: Returns the Club that is the home team for this fixture.
        // HOW: BelongsTo means "this model holds the foreign key."
        //      We specify 'home_team_id' because that's the actual column name.
        // VANILLA EQUIVALENT:
        //      SELECT * FROM clubs WHERE id = {$this->home_team_id}
    {
        return $this->belongsTo(Club::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
        // WHAT: Returns the Club that is the away team.
    {
        return $this->belongsTo(Club::class, 'away_team_id');
    }
}
