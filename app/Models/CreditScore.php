<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditScore extends Model
{
    protected $fillable = [
        'user_id',
        'credit_report_id',
        'bureau',
        'score',
        'score_model',
        'lender_rank',
        'score_scale',
        'risk_factors',
        'report_date',
    ];

    protected $casts = [
        'risk_factors' => 'array',
        'report_date' => 'date',
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
