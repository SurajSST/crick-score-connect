<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory;
    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    // Define the relationship with User model for the second user in the friendship
    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }
}
