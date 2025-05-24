<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class UpdateProfileRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\s-]+$/u',
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user()->id),
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'timezone' => [
                'sometimes',
                'required',
                'string',
                'timezone',
            ],
            'notification_preferences' => [
                'sometimes',
                'array',
            ],
            'notification_preferences.email' => [
                'boolean',
            ],
            'notification_preferences.browser' => [
                'boolean',
            ],
            'avatar' => [
                'nullable',
                'image',
                'max:2048', // 2MB
                'dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name can only contain letters, spaces, and hyphens.',
            'email.regex' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'timezone.timezone' => 'Please select a valid timezone.',
            'avatar.dimensions' => 'Avatar must be between 100x100 and 1000x1000 pixels.',
            'avatar.max' => 'Avatar must not be larger than 2MB.',
        ];
    }
} 