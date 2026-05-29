<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\QuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller for managing questions within a quiz.
 *
 * Provides CRUD operations for questions nested under quizzes.
 * Uses QuestionService for business logic including answer management.
 */
class QuestionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected QuestionService $questionService,
    ) {}

    /**
     * Show the form for creating a new question for the given quiz.
     */
    public function create(Quiz $quiz): View
    {
        return view('questions.create', compact('quiz'));
    }

    /**
     * Store a newly created question for the given quiz.
     */
    public function store(StoreQuestionRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->questionService->createWithAnswers($quiz, $request->validated());

        return redirect()
            ->route('quizzes.show', $quiz)
            ->with('success', 'Question created successfully.');
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Quiz $quiz, Question $question): View
    {
        $question->load('answers');

        return view('questions.edit', compact('quiz', 'question'));
    }

    /**
     * Update the specified question for the given quiz.
     */
    public function update(UpdateQuestionRequest $request, Quiz $quiz, Question $question): RedirectResponse
    {
        $this->questionService->updateWithAnswers($question, $request->validated());

        return redirect()
            ->route('quizzes.show', $quiz)
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified question from the quiz.
     */
    public function destroy(Quiz $quiz, Question $question): RedirectResponse
    {
        $this->questionService->deleteQuestion($question);

        return redirect()
            ->back()
            ->with('success', 'Question deleted successfully.');
    }
}
