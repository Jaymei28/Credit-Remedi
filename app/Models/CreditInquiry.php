<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditInquiry extends Model
{
    protected $fillable = [
        'user_id',
        'credit_report_id',
        'creditor_name',
        'business_type',
        'inquiry_type',
        'inquiry_date',
        'bureau',
        'remarks',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'inquiry_date' => 'date',
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
