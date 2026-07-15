<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundabilityScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'score',
        'grade',
        'factors',
        'recommendations',
        'strengths',
        'weaknesses',
        'credit_score',
        'debt_to_income_ratio',
        'total_accounts',
        'open_accounts',
        'hard_inquiries',
        'negative_items',
    ];

    protected $casts = [
        'factors' => 'array',
        'recommendations' => 'array',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'debt_to_income_ratio' => 'decimal:2',
    ];

    /**
     * Get the user that owns the fundability score.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lender matches for this score.
     */
    public function lenderMatches(): HasMany
    {
        return $this->hasMany(LenderMatch::class);
    }

    /**
     * Get the grade based on score.
     */
    public function getGradeAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        $score = $this->score;
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    /**
     * Get color class based on grade.
     */
    public function getGradeColorClass(): string
    {
        return match($this->grade) {
            'A' => 'success',
            'B' => 'info',
            'C' => 'warning',
            'D' => 'orange',
            'F' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get score color class.
     */
    public function getScoreColorClass(): string
    {
        if ($this->score >= 80) return 'success';
        if ($this->score >= 70) return 'info';
        if ($this->score >= 60) return 'warning';
        if ($this->score >= 50) return 'orange';
        return 'danger';
    }
}
