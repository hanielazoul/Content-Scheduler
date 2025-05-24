<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Platform;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    /**
     * Authorize a given action against a model.
     *
     * @param  string  $ability
     * @param  mixed  $arguments
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        if (!Auth::user()->can($ability, $arguments)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }

    public function index(Request $request)
    {
        $query = Auth::user()->posts()->with(['platforms']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('platform')) {
            $query->whereHas('platforms', function($q) use ($request) {
                $q->where('platforms.id', $request->platform);
            });
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Generate a unique cache key for this query
        $cacheKey = 'user_' . Auth::id() . '_posts_' . md5($request->fullUrl());

        // Store the cache key for later cleanup
        $keys = Cache::get('user_' . Auth::id() . '_post_keys', []);
        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put('user_' . Auth::id() . '_post_keys', $keys, 3600);
        }

        // Cache the filtered posts for 5 minutes
        $posts = Cache::remember($cacheKey, 300, function() use ($query) {
            return $query->latest()->paginate(10);
        });

        // Cache analytics for 5 minutes
        $analytics = Cache::remember('user_' . Auth::id() . '_analytics', 300, function() {
            $totalPosts = Auth::user()->posts()->count();
            $publishedPosts = Auth::user()->posts()->where('status', Post::STATUS_PUBLISHED)->count();
            $scheduledPosts = Auth::user()->posts()->where('status', Post::STATUS_SCHEDULED)->count();
            $draftPosts = Auth::user()->posts()->where('status', Post::STATUS_DRAFT)->count();

            // Calculate percentages
            $publishedPercentage = $totalPosts > 0 ? ($publishedPosts / $totalPosts) * 100 : 0;
            $scheduledPercentage = $totalPosts > 0 ? ($scheduledPosts / $totalPosts) * 100 : 0;
            $draftPercentage = $totalPosts > 0 ? ($draftPosts / $totalPosts) * 100 : 0;

            // Get platform analytics
            $platforms = Platform::withCount(['posts' => function ($query) {
                $query->where('user_id', Auth::id());
            }])->get();

            return [
                'total_posts' => $totalPosts,
                'published_posts' => $publishedPosts,
                'scheduled_posts' => $scheduledPosts,
                'draft_posts' => $draftPosts,
                'published_percentage' => $publishedPercentage,
                'scheduled_percentage' => $scheduledPercentage,
                'draft_percentage' => $draftPercentage,
                'published_count' => $publishedPosts,
                'scheduled_count' => $scheduledPosts,
                'draft_count' => $draftPosts,
                'platforms' => $platforms
            ];
        });

        // Get all platforms for filter dropdown
        $platforms = Cache::remember('all_platforms', 3600, function() {
            return Platform::all();
        });

        return view('dashboard', compact('posts', 'analytics', 'platforms'));
    }

    public function create()
    {
        // return active user platforms
        $platforms = Auth::user()->platforms()->get();

        return view('posts.create', compact('platforms'));
    }

    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();

        $post = Auth::user()->posts()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'scheduled_time' => $validated['scheduled_time'],
            'status'=> isset($validated['scheduled_time']) ? Post::STATUS_SCHEDULED : Post::STATUS_DRAFT
        ]);

        if ($request->hasFile('image')) {
            $post->addMedia($request->file('image'))
                ->toMediaCollection('image');
        }

        $post->platforms()->attach($validated['platforms']);

        // Clear relevant cache entries
        $this->clearUserCache();

        return redirect()->route('dashboard')
            ->with('success', 'Post scheduled successfully.');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        $platforms = Platform::all();
        return view('posts.edit', compact('post', 'platforms'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $validated = $request->validated();

        $post->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'scheduled_time' => $validated['scheduled_time'],
        ]);

        if ($request->hasFile('image')) {
            $post->clearMediaCollection('image');
            $post->addMedia($request->file('image'))
                ->toMediaCollection('image');
        }

        $post->platforms()->sync($validated['platforms']);

        // Clear relevant cache entries
        $this->clearUserCache();

        return redirect()->route('dashboard')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        // Clear relevant cache entries
        $this->clearUserCache();

        return redirect()->route('dashboard')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Clear all cache entries related to the current user's posts
     */
    protected function clearUserCache()
    {
        $userId = Auth::id();

        // Clear analytics cache
        Cache::forget('user_' . $userId . '_analytics');

        // Clear all cached post queries for this user
        $keys = Cache::get('user_' . $userId . '_post_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget('user_' . $userId . '_post_keys');
    }
}
