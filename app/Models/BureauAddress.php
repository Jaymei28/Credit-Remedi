<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BureauAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'full_address',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Scope a query to only include active bureaus.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
