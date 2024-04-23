<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPUnit\Framework\matches;

class MatchPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'match_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function match()
    {
        return $this->belongsTo(Matches::class, 'match_id');
    }
}
