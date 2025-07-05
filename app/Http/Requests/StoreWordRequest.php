<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreWordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can create words
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'chinese_word' => ['required', 'string', 'max:255'],
            'pinyin' => ['required', 'string', 'max:255'],
            'translation' => ['required', 'string', 'max:255'],
            // Tags:
            // - Can be an array (e.g., from multi-select or tags input)
            // - If it's a single string with comma-separated values, you might need to process it in the controller
            //   For JSON column, it's best if it arrives as an array.
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'chinese_word.required' => 'A Chinese word is required.',
            'pinyin.required' => 'Pinyin is required.',
            'translation.required' => 'A translation is required.',
            'tags.array' => 'Tags must be an array.',
            'tags.*.string' => 'Each tag must be a string.',
            'tags.*.max' => 'Each tag cannot be longer than 50 characters.',
        ];
    }
}