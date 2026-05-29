<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attempt;
use App\Models\RagConversation;
use App\Models\RagMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RAGService
{
    /**
     * Find an existing conversation for this attempt or create a new one.
     */
    public function getOrCreateConversation(User $user, Attempt $attempt): RagConversation
    {
        $conversation = RagConversation::where('user_id', $user->id)
            ->where('attempt_id', $attempt->id)
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Determine a related document (if the quiz has attached documents)
        $documentId = null;
        $quiz = $attempt->quiz;

        if ($quiz && method_exists($quiz, 'documents')) {
            $document = $quiz->documents()->first();
            $documentId = $document?->id;
        }

        return RagConversation::create([
            'user_id' => $user->id,
            'attempt_id' => $attempt->id,
            'document_id' => $documentId,
        ]);
    }

    /**
     * Process a user's question and return the AI assistant's response.
     */
    public function askQuestion(RagConversation $conversation, string $question): RagMessage
    {
        // 1. Save the user's message
        $conversation->ragMessages()->create([
            'role' => 'user',
            'content' => $question,
        ]);

        try {
            // 2-5. Build context and call AI
            [$systemPrompt, $messages] = $this->buildContextPrompt($conversation, $question);
            $aiResponse = $this->callAI($systemPrompt, $messages);
        } catch (\Throwable $e) {
            Log::error('RAG AI call failed.', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            $aiResponse = 'I apologize, but I was unable to process your question at this time. Please try again later.';
        }

        // 6-7. Save and return the assistant's response
        return $conversation->ragMessages()->create([
            'role' => 'assistant',
            'content' => $aiResponse,
        ]);
    }

    /**
     * Build the system prompt and message history for the AI call.
     *
     * @return array{0: string, 1: array<int, array<string, string>>}
     */
    public function buildContextPrompt(RagConversation $conversation, string $question): array
    {
        // Get document context
        $documentContent = '';
        if ($conversation->document_id) {
            $document = $conversation->document;
            if ($document && $document->content_text) {
                $documentContent = $document->content_text;

                // Limit document content to avoid token overflow
                if (mb_strlen($documentContent) > 6000) {
                    $documentContent = mb_substr($documentContent, 0, 6000) . "\n\n[Content truncated...]";
                }
            }
        }

        // Get attempt context — questions answered incorrectly
        $attemptContext = $this->buildAttemptContext($conversation);

        // Build the system prompt
        $systemPrompt = $this->buildSystemPrompt($documentContent, $attemptContext);

        // Get conversation history (last 10 messages)
        $history = $this->getConversationHistory($conversation);

        $messages = [];
        foreach ($history as $message) {
            // Skip the last user message since we'll add the current question
            if ($message->role === 'user' && $message->content === $question) {
                continue;
            }

            $messages[] = [
                'role' => $message->role,
                'content' => $message->content,
            ];
        }

        // Add the current question
        $messages[] = [
            'role' => 'user',
            'content' => $question,
        ];

        return [$systemPrompt, $messages];
    }

    /**
     * Call the AI provider with a conversation-style request.
     *
     * @param  array<int, array<string, string>>  $messages
     *
     * @throws \RuntimeException
     */
    public function callAI(string $systemPrompt, array $messages): string
    {
        $provider = config('services.ai.provider', 'gemini');

        return match ($provider) {
            'openai' => $this->callOpenAI($systemPrompt, $messages),
            default => $this->callGemini($systemPrompt, $messages),
        };
    }

    /**
     * Get the last 10 messages of the conversation, ordered by creation time.
     */
    public function getConversationHistory(RagConversation $conversation): Collection
    {
        return $conversation->ragMessages()
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Call Gemini API with multi-turn conversation format.
     *
     * @param  array<int, array<string, string>>  $messages
     *
     * @throws \RuntimeException
     */
    private function callGemini(string $systemPrompt, array $messages): string
    {
        $apiKey = config('services.ai.gemini.api_key');
        $model = config('services.ai.gemini.model', 'gemini-2.0-flash');

        if (empty($apiKey)) {
            throw new \RuntimeException('Gemini API key is not configured.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Build multi-turn contents array
        $contents = [];

        // Add system instruction as the first user message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]],
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'I understand. I will act as a helpful teaching assistant based on the provided context.']],
        ];

        // Add conversation history
        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';

            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message['content']]],
            ];
        }

        $response = Http::timeout(120)->post($url, [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Gemini API request failed: ' . $response->status() . ' - ' . $response->body()
            );
        }

        $result = $response->json();

        return $result['candidates'][0]['content']['parts'][0]['text']
            ?? throw new \RuntimeException('Unexpected Gemini response structure.');
    }

    /**
     * Call OpenAI API with standard chat completion format.
     *
     * @param  array<int, array<string, string>>  $messages
     *
     * @throws \RuntimeException
     */
    private function callOpenAI(string $systemPrompt, array $messages): string
    {
        $apiKey = config('services.ai.openai.api_key');
        $model = config('services.ai.openai.model', 'gpt-4o-mini');

        if (empty($apiKey)) {
            throw new \RuntimeException('OpenAI API key is not configured.');
        }

        // Build messages array with system prompt
        $chatMessages = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
        ];

        foreach ($messages as $message) {
            $chatMessages[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        $response = Http::withToken($apiKey)
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $chatMessages,
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'OpenAI API request failed: ' . $response->status() . ' - ' . $response->body()
            );
        }

        $result = $response->json();

        return $result['choices'][0]['message']['content']
            ?? throw new \RuntimeException('Unexpected OpenAI response structure.');
    }

    /**
     * Build the attempt context string showing incorrectly answered questions.
     */
    private function buildAttemptContext(RagConversation $conversation): string
    {
        $attempt = $conversation->attempt;

        if (! $attempt) {
            return '';
        }

        $attempt->load([
            'attemptDetails' => fn ($query) => $query->where('is_correct', false),
            'attemptDetails.question',
            'attemptDetails.question.answers' => fn ($query) => $query->where('is_correct', true),
            'attemptDetails.answer',
        ]);

        if ($attempt->attemptDetails->isEmpty()) {
            return 'The student answered all questions correctly. Score: ' . $attempt->score . '/' . $attempt->total_points;
        }

        $context = "Student's Quiz Results (Score: {$attempt->score}/{$attempt->total_points}):\n\n";
        $context .= "Questions answered INCORRECTLY:\n";

        foreach ($attempt->attemptDetails as $index => $detail) {
            $number = $index + 1;
            $question = $detail->question;

            if (! $question) {
                continue;
            }

            $context .= "\n{$number}. Question: {$question->content}\n";

            if ($detail->answer) {
                $context .= "   Student's answer: {$detail->answer->content}\n";
            } elseif ($detail->text_answer) {
                $context .= "   Student's answer: {$detail->text_answer}\n";
            } else {
                $context .= "   Student's answer: (no answer provided)\n";
            }

            $correctAnswers = $question->answers->where('is_correct', true);
            if ($correctAnswers->isNotEmpty()) {
                $context .= '   Correct answer: ' . $correctAnswers->first()->content . "\n";
            }

            if ($question->explanation) {
                $context .= "   Explanation: {$question->explanation}\n";
            }
        }

        return $context;
    }

    /**
     * Build the complete system prompt with document content and attempt context.
     */
    private function buildSystemPrompt(string $documentContent, string $attemptContext): string
    {
        $prompt = <<<PROMPT
You are a helpful and patient teaching assistant. Your role is to help students understand the material they were quizzed on.

## Guidelines:
- Be encouraging and supportive in your responses.
- Explain concepts clearly and provide examples when helpful.
- If the student asks about a question they got wrong, explain why the correct answer is right and why their answer was wrong.
- Use the provided document content as your primary knowledge source.
- If you don't know the answer based on the provided context, say so honestly.
- Keep responses concise but thorough.
PROMPT;

        if ($documentContent !== '') {
            $prompt .= "\n\n## Reference Document Content:\n{$documentContent}";
        }

        if ($attemptContext !== '') {
            $prompt .= "\n\n## Student's Quiz Performance:\n{$attemptContext}";
        }

        return $prompt;
    }
}
