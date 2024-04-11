<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FriendRequest extends Model
{
    use HasFactory;
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Define the relationship with User model for the receiver of the friend request
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
