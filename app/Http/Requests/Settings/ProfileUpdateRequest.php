<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'country' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'native_language' => ['nullable', 'string', 'max:255'],
            'chinese_level' => ['nullable', 'string', 'max:255', Rule::in([
                'Beginner', 
                'Advanced',
                'Native/Fluent',
                'HSK 1',
                'HSK 2',
                'HSK 3',
                'HSK 4',
                'HSK 5',
                'HSK 6',
                'TOCFL N1',
                'TOCFL N2',
                'TOCFL 1',
                'TOCFL 2',
                'TOCFL 3',
                'TOCFL 4',
                'TOCFL 5',
                'TOCFL 6'            
            ])],
        ];
    }
}
