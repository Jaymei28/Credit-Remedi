<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_filename',
        'file_path',
        'extracted_text',
        'personal_info',
        'action_plan',
        'action_plan_ts',
        'total_accounts_count',
        'open_accounts_count',
        'negative_accounts_count',
        'hard_inquiries_count',
    ];

    protected $casts = [
        'personal_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditScores()
    {
        return $this->hasMany(CreditScore::class);
    }

    public function creditAccounts()
    {
        return $this->hasMany(CreditAccount::class);
    }

    public function creditInquiries()
    {
        return $this->hasMany(CreditInquiry::class);
    }

    public function creditPublicRecords()
    {
        return $this->hasMany(CreditPublicRecord::class);
    }
}
