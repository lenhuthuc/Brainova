<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AskRAGQuestionRequest;
use App\Models\Attempt;
use App\Models\RagConversation;
use App\Services\RAGService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for RAG (Retrieval-Augmented Generation) chat.
 *
 * Provides chat functionality scoped to quiz attempts, allowing
 * students to ask questions and receive AI-generated explanations
 * based on relevant documents.
 */
class RAGController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected RAGService $ragService,
    ) {}

    /**
     * Display the RAG chat interface for a given attempt.
     */
    public function chat(Request $request, Attempt $attempt): View
    {
        $user = $request->user();

        // Get or create the conversation for this attempt
        $conversation = RagConversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'attempt_id' => $attempt->id,
            ],
            [
                'document_id' => null,
            ],
        );

        // Load conversation messages
        $conversation->load('messages');

        return view('rag.chat', compact('attempt', 'conversation'));
    }

    /**
     * Process a RAG question and return the AI-generated answer.
     */
    public function ask(AskRAGQuestionRequest $request, Attempt $attempt): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->ragService->askQuestion(
            user: $request->user(),
            attempt: $attempt,
            question: $validated['question'],
            conversationId: $validated['conversation_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'answer' => $result['answer'],
            'conversation_id' => $result['conversation_id'],
        ]);
    }
}
