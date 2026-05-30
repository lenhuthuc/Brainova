<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttemptService
{
    /**
     * Start a new quiz attempt for the given user.
     */
    public function startAttempt(User $user, Quiz $quiz): Attempt
    {
        $totalPoints = $quiz->questions()->sum('points');

        return Attempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'started_at' => Carbon::now(),
            'completed_at' => null,
            'score' => 0,
            'total_points' => $totalPoints,
        ]);
    }

    /**
     * Submit an attempt by processing responses, grading, and marking as completed.
     *
     * @param  array<int, array<string, mixed>>  $responses  Each: {question_id, answer_id?, text_answer?}
     */
    public function submitAttempt(Attempt $attempt, array $responses): Attempt
    {
        return DB::transaction(function () use ($attempt, $responses) {
            $totalScore = 0;

            foreach ($responses as $response) {
                $questionId = $response['question_id'];
                $answerId = $response['answer_id'] ?? null;
                $textAnswer = $response['text_answer'] ?? null;

                // Load the question to determine grading
                $question = $attempt->quiz->questions()->find($questionId);

                if (! $question) {
                    continue;
                }

                $isCorrect = false;
                $pointsEarned = 0;

                if (in_array($question->type, ['multiple_choice', 'true_false'], true)) {
                    if ($answerId !== null) {
                        $answer = Answer::where('id', $answerId)
                            ->where('question_id', $questionId)
                            ->first();

                        if ($answer && $answer->is_correct) {
                            $isCorrect = true;
                            $pointsEarned = (float) $question->points;
                        }
                    }
                }
                // short_answer: mark as needs review (is_correct = false, points_earned = 0)

                $attempt->details()->create([
                    'question_id' => $questionId,
                    'answer_id' => $answerId,
                    'text_answer' => $textAnswer,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]);

                $totalScore += $pointsEarned;
            }

            $attempt->update([
                'score' => $totalScore,
                'completed_at' => Carbon::now(),
            ]);

            return $attempt->fresh();
        });
    }

    /**
     * Get an attempt with all its details eagerly loaded.
     */
    public function getAttemptWithDetails(Attempt $attempt): Attempt
    {
        return $attempt->load([
            'details.question',
            'details.answer',
            'details.question.answers' => fn ($query) => $query->orderBy('sort_order'),
        ]);
    }

    /**
     * Get the quiz attempt history for a user, ordered by latest.
     */
    public function getHistoryForUser(User $user): Collection
    {
        return Attempt::where('user_id', $user->id)
            ->with('quiz:id,title,description')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Check if the user has an active (uncompleted) attempt for the given quiz.
     */
    public function hasActiveAttempt(User $user, Quiz $quiz): ?Attempt
    {
        return Attempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->whereNull('completed_at')
            ->latest('started_at')
            ->first();
    }
}
