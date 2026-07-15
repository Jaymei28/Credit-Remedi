<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BotPrompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'content',
        'category',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get prompt by key with caching
     */
    public static function getByKey(string $key): ?string
    {
        return Cache::remember("bot_prompt_{$key}", 3600, function () use ($key) {
            $prompt = self::where('key', $key)
                ->where('active', true)
                ->first();
            
            return $prompt ? $prompt->content : null;
        });
    }

    /**
     * Clear cache when prompt is updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($prompt) {
            Cache::forget("bot_prompt_{$prompt->key}");
        });

        static::deleted(function ($prompt) {
            Cache::forget("bot_prompt_{$prompt->key}");
        });
    }

    /**
     * Scope for active prompts
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
