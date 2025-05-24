<?php

namespace App\Http\Requests;

use App\Models\Platform;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'platforms.*' => 'exists:platforms,id',
            'scheduled_time' => 'nullable|date|after:now',
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
        return false;
    }

    private function validatePlatformRequirements($validator)
    {
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
    }
}
