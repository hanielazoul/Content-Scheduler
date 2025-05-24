<?php

namespace App\Http\Requests\Api;

use App\Models\Platform;
use Illuminate\Validation\Rule;

class StorePostApiRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'platforms' => 'required|array',
            'platforms.*' =>'required|integer|exists:platforms,id',
            'scheduled_time' => 'required|date|after:now',
            'status' => ['required', Rule::in(['draft', 'scheduled'])],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if Instagram is selected and image is required
            $instagram = Platform::where('name', 'Instagram')->first();
            if (in_array($instagram->id, $this->platforms) && !$this->hasFile('image')) {
                $validator->errors()->add('image', 'An image is required for Instagram posts.');
            }

            // Check Twitter character limit
            $twitter = Platform::where('name', 'Twitter')->first();
            if (in_array($twitter->id, $this->platforms) && strlen($this->content) > 280) {
                $validator->errors()->add('content', 'Twitter posts cannot exceed 280 characters.');
            }
        });
    }
} 