<?php

namespace App\Http\Requests;

use App\Models\Platform;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends BasePostRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'platforms' => 'required|array',
            'platforms.*' => 'exists:platforms,id',
            'scheduled_time' => 'required|date|after:now',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validatePlatformRequirements($validator);
        });
    }

    protected function hasExistingImage(): bool
    {
        $post = $this->route('post');
        return $post->hasMedia('image');
    }
} 