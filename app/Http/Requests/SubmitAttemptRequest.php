<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for submitting a quiz attempt.
 *
 * Validates the responses array containing question answers.
 * Any authenticated user can submit an attempt.
 */
class SubmitAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'responses' => 'required|array|min:1',
            'responses.*.question_id' => 'required|integer|exists:questions,id',
            'responses.*.answer_id' => 'nullable|integer|exists:answers,id',
            'responses.*.text_answer' => 'nullable|string',
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
            'responses.required' => 'You must provide at least one response.',
            'responses.min' => 'You must answer at least one question.',
            'responses.*.question_id.required' => 'Each response must reference a question.',
            'responses.*.question_id.exists' => 'One or more referenced questions do not exist.',
            'responses.*.answer_id.exists' => 'One or more selected answers do not exist.',
        ];
    }
}
