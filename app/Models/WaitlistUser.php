<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class WaitlistUser extends Model
{
    protected $fillable = [
        'name', 'email', 'challenge', 'usage', 'timeline', 'referrer_id', 'referral_count', 'is_qualified'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Generate a unique referral code
            do {
                $code = Str::random(8); // You can also use $user->id after saving
            } while (self::where('referral_code', $code)->exists());

            $user->referral_code = $code;
        });

        
    }
    
    public function referrer()
    {
        return $this->belongsTo(WaitlistUser::class, 'referrer_id');
    }

    public function referrals()
    {
        return $this->hasMany(self::class, 'referrer_id', 'referral_code');
    }

}
