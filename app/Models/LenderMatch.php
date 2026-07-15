<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LenderMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lender_id',
        'fundability_score_id',
        'match_score',
        'approval_likelihood',
        'estimated_apr_min',
        'estimated_apr_max',
        'match_reasons',
    ];

    protected $casts = [
        'match_reasons' => 'array',
        'estimated_apr_min' => 'decimal:2',
        'estimated_apr_max' => 'decimal:2',
    ];

    /**
     * Get the user that owns the lender match.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lender for this match.
     */
    public function lender(): BelongsTo
    {
        return $this->belongsTo(Lender::class);
    }

    /**
     * Get the fundability score for this match.
     */
    public function fundabilityScore(): BelongsTo
    {
        return $this->belongsTo(FundabilityScore::class);
    }

    /**
     * Get likelihood badge color.
     */
    public function getLikelihoodBadgeColor(): string
    {
        return match($this->approval_likelihood) {
            'high' => 'success',
            'medium' => 'warning',
            'low' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get likelihood icon.
     */
    public function getLikelihoodIcon(): string
    {
        return match($this->approval_likelihood) {
            'high' => 'check-circle-fill',
            'medium' => 'dash-circle-fill',
            'low' => 'x-circle-fill',
            default => 'circle-fill',
        };
    }

    /**
     * Get formatted likelihood text.
     */
    public function getFormattedLikelihood(): string
    {
        return match($this->approval_likelihood) {
            'high' => 'High Approval Odds',
            'medium' => 'Moderate Approval Odds',
            'low' => 'Lower Approval Odds',
            default => 'Unknown',
        };
    }
}
