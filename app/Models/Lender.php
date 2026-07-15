<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lender extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'bureau_pull',
        'min_credit_score',
        'max_credit_score',
        'recommended_score',
        'score_model',
        'min_amount',
        'max_amount',
        'min_apr',
        'max_apr',
        'intro_apr_months',
        'income_sensitivity',
        'inquiry_sensitivity',
        'application_url',
        'requirements',
        'notes',
        'active',
    ];

    protected $casts = [
        'requirements' => 'array',
        'active' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'min_apr' => 'decimal:2',
        'max_apr' => 'decimal:2',
    ];

    /**
     * Get the lender matches for this lender.
     */
    public function lenderMatches(): HasMany
    {
        return $this->hasMany(LenderMatch::class);
    }

    /**
     * Scope to get active lenders only.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get lender type badge color.
     */
    public function getTypeBadgeColor(): string
    {
        return match($this->type) {
            'bank' => 'primary',
            'credit_union' => 'success',
            'online' => 'info',
            'alternative' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get formatted type name.
     */
    public function getFormattedType(): string
    {
        return match($this->type) {
            'bank' => 'Traditional Bank',
            'credit_union' => 'Credit Union',
            'online' => 'Online Lender',
            'alternative' => 'Alternative Funding',
            default => ucfirst($this->type),
        };
    }
}
