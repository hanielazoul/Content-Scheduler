<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingsApiRequest;
use App\Models\Platform;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SettingsApiController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $platforms = Platform::all();
        $activePlatforms = $user->platforms()->pluck('platforms.id')->toArray();

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

        return response()->json([
            'success' => true,
            'data' => [
                'platforms' => $platforms,
                'active_platforms' => $activePlatforms,
                'analytics' => [
                    'platforms' => $platformStats,
                    'success_rates' => $successRates
                ]
            ]
        ]);
    }

    public function update(UpdateSettingsApiRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = Auth::user();
        $user->platforms()->sync($validated['active_platforms']);

        if (isset($validated['notification_preferences'])) {
            $user->update([
                'notification_preferences' => $validated['notification_preferences']
            ]);
        }

        if (isset($validated['timezone'])) {
            $user->update([
                'timezone' => $validated['timezone']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => [
                'active_platforms' => $validated['active_platforms'],
                'notification_preferences' => $validated['notification_preferences'] ?? $user->notification_preferences,
                'timezone' => $validated['timezone'] ?? $user->timezone
            ]
        ]);
    }
} 