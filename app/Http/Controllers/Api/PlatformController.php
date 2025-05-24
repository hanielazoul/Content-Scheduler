<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Api\UpdateSettingsApiRequest;
use Auth;

class PlatformController extends Controller
{
    public function index()
    {
        // Cache platforms for 1 hour as they rarely change
        $platforms = Cache::remember('all_platforms', 3600, function() {
            return Platform::all();
        });
        
        return response()->json($platforms);
    }

    public function userList()
    {
        // Cache platforms for 1 hour as they rarely change
        $activePlatforms = auth()->user()->platforms()->get();
        
        return response()->json([
            'success' => true,
            'data' => $activePlatforms
        ]);
    }

    public function update(UpdateSettingsApiRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();

        $user->platforms()->sync($validated['active_platforms']);

        // Clear relevant cache entries
        Cache::forget('user_' . $user->id . '_settings_analytics');
        Cache::forget('user_' . $user->id . '_analytics'); // Clear dashboard analytics as well

        return response()->json([
            'suscees'=>true,
            'data'=>[]
        ]);
    }
}
