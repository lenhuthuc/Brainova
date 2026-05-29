<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for asking a RAG (Retrieval-Augmented Generation) question.
 *
 * Validates the question content and optional conversation context.
 * Any authenticated user can ask RAG questions.
 */
class AskRAGQuestionRequest extends FormRequest
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
            'question' => 'required|string|max:1000',
            'conversation_id' => 'nullable|integer|exists:rag_conversations,id',
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
            'question.required' => 'Please enter a question.',
            'question.max' => 'The question must not exceed 1000 characters.',
            'conversation_id.exists' => 'The specified conversation does not exist.',
        ];
    }
}
