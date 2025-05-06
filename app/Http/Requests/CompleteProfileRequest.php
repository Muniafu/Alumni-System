<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $user = $this->user();
        $rules = [];

        if ($user->isAlumni()) {
            $rules = [
                'graduation_year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
                'current_position' => 'required|string|max:255',
                'skills' => 'required|array|min:1',
                'skills.*' => 'exists:skills,id',
                'linkedin_url' => 'nullable|url',
                'phone' => 'required|string|max:20',
            ];
        } elseif ($user->isEmployer()) {
            $rules = [
                'company_name' => 'required|string|max:255',
                'company_size' => 'required|string|max:255',
                'industry' => 'required|string|max:255',
                'website' => 'required|url',
                'contact_person' => 'required|string|max:255',
                'contact_position' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ];
        }

        return $rules;
    }

}
