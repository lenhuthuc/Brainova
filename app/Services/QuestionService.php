<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class QuestionService
{
    /**
     * Create a question and its answers within a database transaction.
     *
     * @param  array<string, mixed>  $data  Expected keys: content, type, points, explanation, answers
     */
    public function createWithAnswers(Quiz $quiz, array $data): Question
    {
        return DB::transaction(function () use ($quiz, $data) {
            $maxSortOrder = $quiz->questions()->max('sort_order') ?? 0;

            /** @var Question $question */
            $question = $quiz->questions()->create([
                'content' => $data['content'],
                'type' => $data['type'],
                'points' => $data['points'] ?? 1,
                'explanation' => $data['explanation'] ?? null,
                'sort_order' => $maxSortOrder + 1,
            ]);

            if (! empty($data['answers']) && is_array($data['answers'])) {
                foreach ($data['answers'] as $index => $answerData) {
                    $question->answers()->create([
                        'content' => $answerData['content'],
                        'is_correct' => $answerData['is_correct'] ?? false,
                        'sort_order' => $index,
                    ]);
                }
            }

            return $question->load('answers');
        });
    }

    /**
     * Update a question and sync its answers (delete old, create new) within a transaction.
     *
     * @param  array<string, mixed>  $data  Expected keys: content, type, points, explanation, answers
     */
    public function updateWithAnswers(Question $question, array $data): Question
    {
        return DB::transaction(function () use ($question, $data) {
            $question->update([
                'content' => $data['content'] ?? $question->content,
                'type' => $data['type'] ?? $question->type,
                'points' => $data['points'] ?? $question->points,
                'explanation' => $data['explanation'] ?? $question->explanation,
            ]);

            // Delete old answers and create new ones
            if (isset($data['answers']) && is_array($data['answers'])) {
                $question->answers()->delete();

                foreach ($data['answers'] as $index => $answerData) {
                    $question->answers()->create([
                        'content' => $answerData['content'],
                        'is_correct' => $answerData['is_correct'] ?? false,
                        'sort_order' => $index,
                    ]);
                }
            }

            return $question->fresh(['answers']);
        });
    }

    /**
     * Delete a question (answers cascade via DB foreign key).
     */
    public function deleteQuestion(Question $question): bool
    {
        return (bool) $question->delete();
    }

    /**
     * Update the sort_order for each question in the quiz.
     *
     * @param  array<int, int>  $order  Array of question IDs in desired order (index => question_id)
     */
    public function reorderQuestions(Quiz $quiz, array $order): void
    {
        DB::transaction(function () use ($quiz, $order) {
            foreach ($order as $sortOrder => $questionId) {
                $quiz->questions()
                    ->where('id', $questionId)
                    ->update(['sort_order' => $sortOrder]);
            }
        });
    }
}
