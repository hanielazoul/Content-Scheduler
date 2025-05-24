<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:platforms,name,' . $this->route('platform'),
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'character_limit' => 'nullable|integer|min:0',
            'requires_image' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'This platform name is already taken.',
            'character_limit.min' => 'Character limit must be a positive number.',
        ];
    }
} 