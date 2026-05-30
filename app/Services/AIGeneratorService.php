<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIGeneratorService
{
    /**
     * Generate quiz questions from document content using AI.
     *
     * @param  string  $documentContent  The source text to generate questions from
     * @param  int  $numberOfQuestions  Number of questions to generate
     * @param  string  $questionType  Type: multiple_choice, true_false, short_answer
     * @param  string  $difficulty  Difficulty level: easy, medium, hard
     * @return array<int, array<string, mixed>>  Array of question data
     */
    public function generateQuestions(
        string $documentContent,
        int $numberOfQuestions = 5,
        string $questionType = 'multiple_choice',
        string $difficulty = 'medium'
    ): array {
        $provider = config('services.ai.provider', 'gemini');

        try {
            $chunks = mb_strlen($documentContent) <= 8000
                ? [$documentContent]
                : $this->chunkContent($documentContent, 8000);

            // Limit chunks to at most $numberOfQuestions (1 question minimum per chunk)
            $chunks = array_slice($chunks, 0, $numberOfQuestions);
            $chunkCount = count($chunks);
            $questionsPerChunk = (int) ceil($numberOfQuestions / $chunkCount);

            $allQuestions = [];

            foreach ($chunks as $chunk) {
                $prompt = $this->buildPrompt($chunk, $questionsPerChunk, $questionType, $difficulty);

                $response = match ($provider) {
                    'openai' => $this->callOpenAI($prompt),
                    default => $this->callGemini($prompt),
                };

                $questions = $this->parseResponse($response);
                $allQuestions = array_merge($allQuestions, $questions);
            }

            $questions = array_slice($allQuestions, 0, $numberOfQuestions);

            if (empty($questions)) {
                Log::warning('AI returned empty question set.', [
                    'provider' => $provider,
                    'question_type' => $questionType,
                    'count_requested' => $numberOfQuestions,
                ]);

                return [];
            }

            return $questions;
        } catch (\RuntimeException $e) {
            Log::error('AI question generation failed.', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (\Throwable $e) {
            Log::error('Unexpected error during AI question generation.', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException('Lỗi không xác định khi gọi AI: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Build a detailed prompt for the AI to generate quiz questions in JSON format.
     */
    public function buildPrompt(string $content, int $count, string $type, string $difficulty): string
    {
        $typeLabel = match ($type) {
            'multiple_choice' => 'multiple choice (with 4 answer options, exactly one correct)',
            'true_false' => 'true/false (with 2 answer options: True and False, exactly one correct)',
            'short_answer' => 'short answer (no answer options needed, provide the expected answer in explanation)',
            default => 'multiple choice (with 4 answer options, exactly one correct)',
        };

        $difficultyInstruction = match ($difficulty) {
            'easy' => 'Create straightforward questions that test basic recall and understanding.',
            'hard' => 'Create challenging questions that require deep analysis, critical thinking, and synthesis of concepts.',
            default => 'Create moderately challenging questions that test comprehension and application of concepts.',
        };

        $languageInstruction = $this->detectLanguageInstruction($content);

        return <<<PROMPT
You are an expert quiz generator. Generate exactly {$count} {$typeLabel} questions based on the following content.

## Instructions:
- {$difficultyInstruction}
- Each question must be directly related to the provided content.
- {$languageInstruction}
- Provide a clear explanation for each question's correct answer.
- For multiple choice: provide exactly 4 options with exactly 1 correct answer.
- For true/false: provide exactly 2 options ("True" and "False") with exactly 1 correct answer.
- For short answer: leave the answers array empty and put the expected answer in the explanation.

## Output Format:
Respond with ONLY a valid JSON array. No markdown, no extra text. The format must be:

[
  {
    "content": "The question text here?",
    "type": "{$type}",
    "explanation": "Explanation of the correct answer.",
    "points": 1,
    "answers": [
      {"content": "Option A text", "is_correct": false},
      {"content": "Option B text", "is_correct": true},
      {"content": "Option C text", "is_correct": false},
      {"content": "Option D text", "is_correct": false}
    ]
  }
]

## Source Content:
{$content}
PROMPT;
    }

    /**
     * Call the Gemini API to generate content.
     *
     * @throws \RuntimeException
     */
    public function callGemini(string $prompt): string
    {
        $apiKey = config('services.ai.gemini.api_key');
        $model = config('services.ai.gemini.model', 'gemini-2.0-flash');

        if (empty($apiKey)) {
            throw new \RuntimeException('Gemini API key is not configured.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(120)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 4096,
            ],
        ]);

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->json() ?? [];

            if ($status === 429) {
                $retryDelay = data_get($body, 'error.details.2.retryDelay', '60s');
                Log::warning('Gemini API rate limit / quota exceeded.', [
                    'model' => $model,
                    'http_status' => $status,
                    'retry_delay' => $retryDelay,
                    'violations' => data_get($body, 'error.details.1.violations', []),
                ]);
                throw new \RuntimeException(
                    "Gemini API vượt quá giới hạn quota (free tier). Vui lòng thử lại sau {$retryDelay}."
                );
            }

            Log::error('Gemini API request failed.', [
                'model' => $model,
                'http_status' => $status,
                'response_body' => $response->body(),
            ]);

            throw new \RuntimeException(
                "Gemini API trả về lỗi HTTP {$status}: " . $response->body()
            );
        }

        $result = $response->json();

        if (! isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            Log::error('Unexpected Gemini response structure.', [
                'model' => $model,
                'response' => $result,
            ]);
            throw new \RuntimeException('Cấu trúc phản hồi từ Gemini không hợp lệ.');
        }

        return $result['candidates'][0]['content']['parts'][0]['text'];
    }

    /**
     * Call the OpenAI API to generate content.
     *
     * @throws \RuntimeException
     */
    public function callOpenAI(string $prompt): string
    {
        $apiKey = config('services.ai.openai.api_key');
        $model = config('services.ai.openai.model', 'gpt-4o-mini');

        if (empty($apiKey)) {
            throw new \RuntimeException('OpenAI API key is not configured.');
        }

        $response = Http::withToken($apiKey)
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a quiz generator. Always respond with valid JSON only.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->failed()) {
            $status = $response->status();

            if ($status === 429) {
                $retryAfter = $response->header('Retry-After') ?? '60';
                Log::warning('OpenAI API rate limit exceeded.', [
                    'model' => $model,
                    'http_status' => $status,
                    'retry_after' => $retryAfter,
                ]);
                throw new \RuntimeException(
                    "OpenAI API vượt quá giới hạn quota. Vui lòng thử lại sau {$retryAfter} giây."
                );
            }

            Log::error('OpenAI API request failed.', [
                'model' => $model,
                'http_status' => $status,
                'response_body' => $response->body(),
            ]);

            throw new \RuntimeException(
                "OpenAI API trả về lỗi HTTP {$status}: " . $response->body()
            );
        }

        $result = $response->json();

        if (! isset($result['choices'][0]['message']['content'])) {
            Log::error('Unexpected OpenAI response structure.', [
                'model' => $model,
                'response' => $result,
            ]);
            throw new \RuntimeException('Cấu trúc phản hồi từ OpenAI không hợp lệ.');
        }

        return $result['choices'][0]['message']['content'];
    }

    /**
     * Parse the AI response and extract the structured questions array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parseResponse(string $response): array
    {
        // Remove markdown code block wrappers if present
        $cleaned = $response;

        if (preg_match('/```(?:json)?\s*\n?(.*?)\n?\s*```/s', $cleaned, $matches)) {
            $cleaned = $matches[1];
        }

        $cleaned = trim($cleaned);

        // Try to decode the JSON
        $decoded = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Attempt to find a JSON array within the response
            if (preg_match('/\[.*\]/s', $cleaned, $arrayMatch)) {
                $decoded = json_decode($arrayMatch[0], true);
            }
        }

        if (! is_array($decoded)) {
            Log::warning('Failed to parse AI response as JSON.', [
                'response_preview' => mb_substr($response, 0, 500),
            ]);

            return [];
        }

        // Handle responses wrapped in an object (e.g., {"questions": [...]})
        if (isset($decoded['questions']) && is_array($decoded['questions'])) {
            $decoded = $decoded['questions'];
        }

        // Validate and normalize the structure
        return $this->validateQuestions($decoded);
    }

    /**
     * Split content into chunks by paragraphs, respecting the max character limit.
     *
     * @return array<int, string>
     */
    public function chunkContent(string $content, int $maxChars = 8000): array
    {
        $paragraphs = preg_split('/\n\s*\n/', $content);

        if ($paragraphs === false) {
            return [mb_substr($content, 0, $maxChars)];
        }

        $chunks = [];
        $currentChunk = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            // If a single paragraph exceeds the limit, split it further
            if (mb_strlen($paragraph) > $maxChars) {
                if ($currentChunk !== '') {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = '';
                }

                $chunks[] = mb_substr($paragraph, 0, $maxChars);

                continue;
            }

            if (mb_strlen($currentChunk) + mb_strlen($paragraph) + 2 > $maxChars) {
                if ($currentChunk !== '') {
                    $chunks[] = trim($currentChunk);
                }
                $currentChunk = $paragraph;
            } else {
                $currentChunk .= ($currentChunk !== '' ? "\n\n" : '') . $paragraph;
            }
        }

        if ($currentChunk !== '') {
            $chunks[] = trim($currentChunk);
        }

        return $chunks ?: [mb_substr($content, 0, $maxChars)];
    }

    /**
     * Detect the language of the content and return an appropriate instruction.
     */
    private function detectLanguageInstruction(string $content): string
    {
        // Simple heuristic: check for Vietnamese characters
        $vietnamesePattern = '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/ui';

        if (preg_match($vietnamesePattern, $content)) {
            return 'Generate questions in Vietnamese language since the content is in Vietnamese.';
        }

        return 'Generate questions in the same language as the source content.';
    }

    /**
     * Validate and normalize the parsed questions array.
     *
     * @param  array<int, mixed>  $questions
     * @return array<int, array<string, mixed>>
     */
    private function validateQuestions(array $questions): array
    {
        $validated = [];

        foreach ($questions as $question) {
            if (! is_array($question)) {
                continue;
            }

            if (empty($question['content'])) {
                continue;
            }

            $type = $question['type'] ?? 'multiple_choice';
            if (! in_array($type, ['multiple_choice', 'true_false', 'short_answer'], true)) {
                $type = 'multiple_choice';
            }

            $answers = [];
            if (isset($question['answers']) && is_array($question['answers'])) {
                foreach ($question['answers'] as $answer) {
                    if (! is_array($answer) || empty($answer['content'])) {
                        continue;
                    }

                    $answers[] = [
                        'content' => (string) $answer['content'],
                        'is_correct' => (bool) ($answer['is_correct'] ?? false),
                    ];
                }
            }

            $validated[] = [
                'content' => (string) $question['content'],
                'type' => $type,
                'explanation' => (string) ($question['explanation'] ?? ''),
                'points' => (int) ($question['points'] ?? 1),
                'answers' => $answers,
            ];
        }

        return $validated;
    }
}
