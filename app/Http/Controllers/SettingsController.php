<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\Post;
use App\Http\Requests\UpdateSettingsRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Cache platforms for 1 hour as they rarely change
        $platforms = Cache::remember('all_platforms', 3600, function() {
            return Platform::all();
        });

        $activePlatforms = $user->platforms()->pluck('platforms.id')->toArray();

        // Cache analytics for 5 minutes
        $analytics = Cache::remember('user_' . $user->id . '_settings_analytics', 300, function() use ($user) {
            // Get platform analytics
            $platformStats = Platform::withCount(['posts' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])->get();

            // Calculate success rates
            $successRates = $platformStats->mapWithKeys(function ($platform) use ($user) {
                $totalPosts = $platform->posts_count;
                $successfulPosts = Post::where('user_id', $user->id)
                    ->whereHas('platforms', function ($query) use ($platform) {
                        $query->where('platforms.id', $platform->id);
                    })
                    ->where('status', Post::STATUS_PUBLISHED)
                    ->count();

                $successRate = $totalPosts > 0 ? ($successfulPosts / $totalPosts) * 100 : 0;

                return [$platform->name => $successRate];
            });

            return [
                'platforms' => $platformStats,
                'success_rates' => $successRates
            ];
        });

        return view('settings', compact('platforms', 'activePlatforms', 'analytics'));
    }

    public function update(UpdateSettingsRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $user->platforms()->sync($validated['active_platforms']);

        // Clear relevant cache entries
        Cache::forget('user_' . $user->id . '_settings_analytics');
        Cache::forget('user_' . $user->id . '_analytics'); // Clear dashboard analytics as well

        return redirect()->route('settings')
            ->with('success', 'Settings updated successfully.');
    }
    
}
