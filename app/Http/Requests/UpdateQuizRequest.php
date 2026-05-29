<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for updating an existing quiz.
 *
 * Validates quiz update data and ensures only the quiz owner
 * can update it.
 */
class UpdateQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Only the quiz owner can update it.
     */
    public function authorize(): bool
    {
        return auth()->user()->id === $this->route('quiz')->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'time_limit_minutes' => 'nullable|integer|min:1|max:180',
            'is_published' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The quiz title is required.',
            'title.max' => 'The quiz title must not exceed 255 characters.',
            'description.max' => 'The quiz description must not exceed 2000 characters.',
            'time_limit_minutes.min' => 'The time limit must be at least 1 minute.',
            'time_limit_minutes.max' => 'The time limit must not exceed 180 minutes.',
        ];
    }
}
