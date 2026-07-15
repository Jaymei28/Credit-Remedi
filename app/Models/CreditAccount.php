<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditAccount extends Model
{
    protected $fillable = [
        'user_id',
        'credit_report_id',
        'creditor_name',
        'account_number',
        'account_type',
        'account_status',
        'bureau',
        'credit_limit',
        'current_balance',
        'monthly_payment',
        'original_amount',
        'term_months',
        'date_opened',
        'date_closed',
        'last_payment_date',
        'date_reported',
        'payment_status',
        'months_reviewed',
        'times_30_days_late',
        'times_60_days_late',
        'times_90_days_late',
        'responsibility',
        'remarks',
        'account_condition',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'date_opened' => 'date',
        'date_closed' => 'date',
        'last_payment_date' => 'date',
        'date_reported' => 'date',
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'original_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditReport()
    {
        return $this->belongsTo(CreditReport::class);
    }
}
