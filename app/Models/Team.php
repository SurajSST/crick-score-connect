<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'team_players');
    }

    public function matches()
    {
        return $this->hasMany(Matches::class, 'team1_id')->orWhere('team2_id', $this->id);
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
