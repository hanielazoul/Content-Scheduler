<?php

namespace App\Http\Requests\Api;

use App\Models\Platform;
use App\Models\Post;
use Illuminate\Validation\Rule;

class UpdatePostApiRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
    }

    public function rules(): array
    {
        $post = $this->route('post');
        
        return [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image' => 'nullable|image|max:2048',
            'platforms' => 'sometimes|required|array',
            'platforms.*' => [
                'required',
                Rule::exists('platforms', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'scheduled_time' => [
                'sometimes',
                'required',
                'date',
                function ($attribute, $value, $fail) use ($post) {
                    if ($post->status === Post::STATUS_PUBLISHED) {
                        $fail('Cannot update scheduled time for published posts.');
                    }
                },
                'after:now',
            ],
            'status' => [
                'sometimes',
                'required',
                Rule::in(['draft', 'scheduled']),
                function ($attribute, $value, $fail) use ($post) {
                    if ($post->status === Post::STATUS_PUBLISHED) {
                        $fail('Cannot change status of published posts.');
                    }
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $post = $this->route('post');

            // Check if Instagram is selected and image is required
            $instagram = Platform::where('name', 'Instagram')->first();
            if (in_array($instagram->id, $this->platforms ?? []) && !$this->hasFile('image') && !$post->hasMedia('image')) {
                $validator->errors()->add('image', 'An image is required for Instagram posts.');
            }

            // Check Twitter character limit
            $twitter = Platform::where('name', 'Twitter')->first();
            if (in_array($twitter->id, $this->platforms ?? []) && strlen($this->content) > 280) {
                $validator->errors()->add('content', 'Twitter posts cannot exceed 280 characters.');
            }
        });
    }
} 