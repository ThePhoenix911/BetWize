<?php
// app/Models/Follow.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Follow extends Model
{
    protected $fillable = ['user_id', 'followable_id', 'followable_type'];

    public function user(): BelongsTo
        // WHAT: Returns the User who created this follow.
        // WHY: Lets you do $follow->user to get the follower's details.
    {
        return $this->belongsTo(User::class);
    }

    public function followable(): MorphTo
        // WHAT: Returns the Club, League, or Cup being followed.
        // HOW: Laravel reads followable_type to know which model to load,
        //      then uses followable_id to find the specific record.
        // WHY: One method that works for ALL three follow types.
        // VANILLA EQUIVALENT:
        //      if ($this->followable_type === 'App\Models\Club') {
        //          return Club::find($this->followable_id);
        //      } elseif ($this->followable_type === 'App\Models\League') {
        //          return League::find($this->followable_id);
        //      } else {
        //          return Cup::find($this->followable_id);
        //      }
    {
        return $this->morphTo();
    }
}
