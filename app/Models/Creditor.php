<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creditor extends Model
{
    protected $fillable = ['name', 'type', 'usage_count'];
    
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}
