<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class QuizService
{
    /**
     * Get all quizzes created by the given user, ordered by latest.
     */
    public function getAllForUser(User $user): Collection
    {
        return $user->quizzes()
            ->withCount('questions')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get all published quizzes with their question count.
     */
    public function getPublishedQuizzes(): Collection
    {
        return Quiz::where('is_published', true)
            ->withCount('questions')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Find a quiz with its questions and answers eager loaded.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findWithQuestions(int $id): Quiz
    {
        return Quiz::with([
            'questions' => fn ($query) => $query->orderBy('sort_order'),
            'questions.answers' => fn ($query) => $query->orderBy('sort_order'),
        ])->findOrFail($id);
    }

    /**
     * Create a new quiz for the given user.
     *
     * @param  array<string, mixed>  $data
     */
    public function createQuiz(array $data, User $user): Quiz
    {
        return $user->quizzes()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'is_published' => $data['is_published'] ?? false,
        ]);
    }

    /**
     * Update an existing quiz.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateQuiz(Quiz $quiz, array $data): Quiz
    {
        $quiz->update([
            'title' => $data['title'] ?? $quiz->title,
            'description' => $data['description'] ?? $quiz->description,
            'time_limit_minutes' => $data['time_limit_minutes'] ?? $quiz->time_limit_minutes,
            'is_published' => $data['is_published'] ?? $quiz->is_published,
        ]);

        return $quiz->fresh();
    }

    /**
     * Delete a quiz and its related records (cascaded by DB).
     */
    public function deleteQuiz(Quiz $quiz): bool
    {
        return (bool) $quiz->delete();
    }

    /**
     * Toggle the published status of a quiz.
     */
    public function togglePublish(Quiz $quiz): Quiz
    {
        $quiz->update([
            'is_published' => ! $quiz->is_published,
        ]);

        return $quiz->fresh();
    }
}
