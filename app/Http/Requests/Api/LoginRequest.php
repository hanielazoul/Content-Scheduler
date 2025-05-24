<?php

namespace App\Http\Requests\Api;

class LoginRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => [
                'required',
                'string',
            ],
            'remember_me' => [
                'boolean',
            ],
            'device_name' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.regex' => 'Please enter a valid email address.',
            'device_name.required' => 'Device name is required for security purposes.',
        ];
    }
} 