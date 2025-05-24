<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePostApiRequest;
use App\Http\Requests\Api\UpdatePostApiRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PostApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Auth::user()->posts()->with(['platforms']);

        // Filter by status
        if ($request->has('status')) {
            $status = $request->input('status');
            if (in_array($status, ['published', 'scheduled', 'draft'])) {
                $query->where('status', $status);
            }
        }

        // Filter by platform
        if ($request->has('platform')) {
            $platformId = $request->input('platform');
            if (is_numeric($platformId)) {
                $query->whereHas('platforms', function ($q) use ($platformId) {
                    $q->where('platforms.id', $platformId);
                });
            }
        }

        // Filter by date
        if ($request->has('date')) {
            $date = $request->input('date');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $query->whereDate('scheduled_time', $date);
            }
        }

        $posts = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    public function store(StorePostApiRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $post = Auth::user()->posts()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'scheduled_time' => $validated['scheduled_time'],
            'status' => $validated['status'],
        ]);

        if ($request->hasFile('image')) {
            $post->addMedia($request->file('image'))
                ->toMediaCollection('image');
        }

        $post->platforms()->attach($validated['platforms']);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post->load('platforms')
        ], 201);
    }

    public function show(Post $post): JsonResponse
    {
        $this->authorize('view', $post);

        return response()->json([
            'success' => true,
            'data' => $post->load('platforms')
        ]);
    }

    public function update(UpdatePostApiRequest $request, Post $post): JsonResponse
    {
        $validated = $request->validated();

        $post->update($validated);

        if ($request->hasFile('image')) {
            $post->clearMediaCollection('image');
            $post->addMedia($request->file('image'))
                ->toMediaCollection('image');
        }

        if (isset($validated['platforms'])) {
            $post->platforms()->sync($validated['platforms']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post->load('platforms')
        ]);
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);
        
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }
} 