<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotPrompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BotPromptController extends Controller
{
    /**
     * Display a listing of bot prompts.
     */
    public function index(Request $request)
    {
        $query = BotPrompt::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('active', $request->status === 'active');
        }

        $prompts = $query->orderBy('category')->orderBy('order')->paginate(20);
        
        $categories = BotPrompt::select('category')->distinct()->pluck('category');

        return view('admin.bot-prompts.index', compact('prompts', 'categories'));
    }

    /**
     * Show the form for creating a new prompt.
     */
    public function create()
    {
        return view('admin.bot-prompts.create');
    }

    /**
     * Store a newly created prompt.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:bot_prompts,key|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'required|string|max:255',
            'active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $validated['active'] = $request->has('active');

        BotPrompt::create($validated);

        return redirect()->route('admin.bot-prompts.index')
            ->with('success', '✅ Bot prompt created successfully!');
    }

    /**
     * Display the specified prompt.
     */
    public function show(BotPrompt $botPrompt)
    {
        return view('admin.bot-prompts.show', compact('botPrompt'));
    }

    /**
     * Show the form for editing the specified prompt.
     */
    public function edit(BotPrompt $botPrompt)
    {
        return view('admin.bot-prompts.edit', compact('botPrompt'));
    }

    /**
     * Update the specified prompt.
     */
    public function update(Request $request, BotPrompt $botPrompt)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:bot_prompts,key,' . $botPrompt->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'required|string|max:255',
            'active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $validated['active'] = $request->has('active');

        $botPrompt->update($validated);

        // Clear cache
        Cache::forget("bot_prompt_{$botPrompt->key}");

        return redirect()->route('admin.bot-prompts.index')
            ->with('success', '✅ Bot prompt updated successfully!');
    }

    /**
     * Remove the specified prompt.
     */
    public function destroy(BotPrompt $botPrompt)
    {
        Cache::forget("bot_prompt_{$botPrompt->key}");
        $botPrompt->delete();

        return redirect()->route('admin.bot-prompts.index')
            ->with('success', '✅ Bot prompt deleted successfully!');
    }

    /**
     * Toggle active status.
     */
    public function toggle(BotPrompt $botPrompt)
    {
        $botPrompt->update(['active' => !$botPrompt->active]);
        Cache::forget("bot_prompt_{$botPrompt->key}");

        $status = $botPrompt->active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "✅ Prompt {$status} successfully!");
    }

    /**
     * Clear all prompt caches.
     */
    public function clearCache()
    {
        $prompts = BotPrompt::all();
        foreach ($prompts as $prompt) {
            Cache::forget("bot_prompt_{$prompt->key}");
        }

        return redirect()->back()
            ->with('success', '✅ All prompt caches cleared!');
    }
}
