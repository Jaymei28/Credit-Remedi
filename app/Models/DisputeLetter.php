<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisputeLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'letter_content',
        'letter_content_2',
        'letter_content_2_ts',
        'posted_1',
        'posted_1_ts',
        'posted_2',
        'posted_2_ts',
        'credit_bureau',
        'credit_item_type',
        'creditor_name',
        'account_number',
        'dispute_reason',
        'desired_resolution',
        'sent',
        'sent_date',
        'sent_ts',
        'phase'
    ];

    protected $casts = [
        'sent' => 'boolean',
        'sent_date' => 'date',
        'sent_ts' => 'datetime',
    ];
    

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    
}


