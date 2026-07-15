<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPublicRecord extends Model
{
    protected $fillable = [
        'user_id',
        'credit_report_id',
        'record_type',
        'status',
        'bureau',
        'amount',
        'balance',
        'filed_date',
        'satisfied_date',
        'closing_date',
        'court_name',
        'case_number',
        'plaintiff',
        'attorney',
        'remarks',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'filed_date' => 'date',
        'satisfied_date' => 'date',
        'closing_date' => 'date',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
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
