<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for storing a new question.
 *
 * Validates question data including nested answers array.
 * Answers are required unless the question type is short_answer.
 */
class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role === 'teacher';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false,short_answer',
            'points' => 'required|integer|min:1|max:100',
            'explanation' => 'nullable|string',
            'answers' => array_filter(['required_unless:type,short_answer', 'nullable', 'array', $this->input('type') !== 'short_answer' ? 'min:2' : null]),
            'answers.*.content' => 'required_with:answers|string',
            'answers.*.is_correct' => 'required_with:answers|boolean',
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
            'answers.required_unless' => 'Answers are required for multiple choice and true/false questions.',
            'answers.min' => 'At least 2 answer options are required.',
            'answers.*.content.required_with' => 'Each answer must have content.',
            'answers.*.is_correct.required_with' => 'Each answer must specify whether it is correct.',
            'content.required' => 'The question content is required.',
            'type.required' => 'The question type is required.',
            'type.in' => 'The question type must be multiple_choice, true_false, or short_answer.',
            'points.required' => 'The points value is required.',
            'points.min' => 'The points value must be at least 1.',
            'points.max' => 'The points value must not exceed 100.',
        ];
    }
}
