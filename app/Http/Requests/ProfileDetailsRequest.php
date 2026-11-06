<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileDetailsRequest extends FormRequest
{
    /**
     * Route lives inside ['auth', 'verified', 'approved'] middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Every field the profile details form can submit. Fields the request
     * omits simply stay absent from validated(), which the controller
     * handles null-safely.
     */
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'current_job' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'interests' => ['nullable', 'string', 'max:1000'],
            'education' => ['nullable', 'string', 'max:255'],
            'certifications' => ['nullable', 'string', 'max:1000'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'url', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }
}
