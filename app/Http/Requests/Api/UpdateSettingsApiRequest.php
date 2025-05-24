<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class UpdateSettingsApiRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active_platforms' => 'required|array',
            'active_platforms.*' => 'required|integer|exists:platforms,id'
        
        ];
    }

    public function messages(): array
    {
        return [
            'active_platforms.required' => 'Please select at least one platform.',
            'active_platforms.*.exists' => 'One or more selected platforms are invalid.',
        ];
    }
} 