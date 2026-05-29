<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for AI-powered question generation.
 *
 * Validates the parameters needed for generating quiz questions
 * from a document using AI. Only teachers are authorized.
 */
class GenerateQuestionsRequest extends FormRequest
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
            'document_id' => 'required|exists:documents,id',
            'quiz_id' => 'required|exists:quizzes,id',
            'number_of_questions' => 'required|integer|min:1|max:20',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,mixed',
            'difficulty' => 'required|in:easy,medium,hard',
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
            'document_id.required' => 'Please select a document.',
            'document_id.exists' => 'The selected document does not exist.',
            'quiz_id.required' => 'Please select a quiz.',
            'quiz_id.exists' => 'The selected quiz does not exist.',
            'number_of_questions.required' => 'Please specify the number of questions to generate.',
            'number_of_questions.min' => 'At least 1 question must be generated.',
            'number_of_questions.max' => 'A maximum of 20 questions can be generated at once.',
            'question_type.required' => 'Please select a question type.',
            'question_type.in' => 'The question type must be multiple_choice, true_false, short_answer, or mixed.',
            'difficulty.required' => 'Please select a difficulty level.',
            'difficulty.in' => 'The difficulty must be easy, medium, or hard.',
        ];
    }
}
