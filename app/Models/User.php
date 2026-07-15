<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Billable; // ← Add Billable here


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'city',
        'state',
        'zipcode',
        'contact_number',
        'role',  // add role here
        'paid_amount',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'plan_type',
        'registration_status',
        'registration_error',
        'payment_attempted_at',
        'ssn_last4',
        'identityiq_enrolled',
        'identityiq_enrolled_at',
        'initial_report_uploaded',
        'initial_report_uploaded_at',
        'onboarding_completed',
        'onboarding_completed_at',
        'ai_analysis_completed',
        'ai_analysis_completed_at',
        'tour_completed'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'paid_amount' => 'decimal:2',
    ];

    // Roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_REGULAR = 'regular';

    // Plan Types
    public const PLAN_STARTER = 'starter';
    public const PLAN_STANDARD = 'standard';
    public const PLAN_PRO = 'pro';
    public const PLAN_PREMIUM = 'premium';

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Get user-friendly plan type label
     */
    public function getPlanTypeLabel(): string
    {
        return match($this->plan_type) {
            self::PLAN_STARTER => 'Starter',
            self::PLAN_STANDARD => 'Standard',
            self::PLAN_PRO => 'Pro',
            self::PLAN_PREMIUM => 'Premium',
            default => 'None',
        };
    }
}
