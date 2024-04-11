<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'dob',
        'phone',
        'address',
        'playerType',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    // Define the relationship with Team model for teams the user is a member of
    public function teamsAsPlayer()
    {
        // Assuming many-to-many relationship with pivot table 'team_players'
        return $this->belongsToMany(Team::class, 'team_players');
    }

    // Define the relationship with BattingStats model for batting stats of the user
    public function battingStats()
    {
        return $this->hasMany(BattingStats::class);
    }

    // Define the relationship with BowlingStats model for bowling stats of the user
    public function bowlingStats()
    {
        return $this->hasMany(BowlingStats::class);
    }

    // Define the relationship with FriendRequest model for friend requests sent by the user
    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    // Define the relationship with FriendRequest model for friend requests received by the user
    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }

    // Define the relationship with Friendship model for friendships of the user
    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'user1_id')->orWhere('user2_id', $this->id);
    }
}
