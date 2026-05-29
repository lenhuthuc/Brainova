<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for managing quizzes.
 *
 * Provides full CRUD operations for quizzes with role-based
 * visibility and ownership checks. Uses QuizService for
 * business logic.
 */
class QuizController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected QuizService $quizService,
    ) {}

    /**
     * Display a listing of quizzes.
     *
     * Teachers see their own quizzes; students see published quizzes.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->role === 'teacher') {
            $quizzes = Quiz::where('user_id', $user->id)
                ->withCount('questions')
                ->latest()
                ->paginate(15);
        } else {
            $quizzes = Quiz::where('is_published', true)
                ->withCount('questions')
                ->latest()
                ->paginate(15);
        }

        return view('quizzes.index', compact('quizzes'));
    }

    /**
     * Show the form for creating a new quiz.
     */
    public function create(): View
    {
        return view('quizzes.create');
    }

    /**
     * Store a newly created quiz in storage.
     */
    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $quiz = $this->quizService->createQuiz($request->validated(), $request->user());

        return redirect()
            ->route('quizzes.show', $quiz)
            ->with('success', 'Quiz created successfully.');
    }

    /**
     * Display the specified quiz.
     */
    public function show(Quiz $quiz): View
    {
        $quiz->load('questions.answers');

        return view('quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the specified quiz.
     */
    public function edit(Quiz $quiz): View
    {
        if (auth()->user()->id !== $quiz->user_id) {
            abort(403, 'You are not authorized to edit this quiz.');
        }

        return view('quizzes.edit', compact('quiz'));
    }

    /**
     * Update the specified quiz in storage.
     */
    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->quizService->updateQuiz($quiz, $request->validated());

        return redirect()
            ->back()
            ->with('success', 'Quiz updated successfully.');
    }

    /**
     * Remove the specified quiz from storage.
     */
    public function destroy(Quiz $quiz): RedirectResponse
    {
        if (auth()->user()->id !== $quiz->user_id) {
            abort(403, 'You are not authorized to delete this quiz.');
        }

        $this->quizService->deleteQuiz($quiz);

        return redirect()
            ->route('quizzes.index')
            ->with('success', 'Quiz deleted successfully.');
    }

    /**
     * Toggle the published status of the specified quiz.
     */
    public function togglePublish(Quiz $quiz): RedirectResponse
    {
        $this->quizService->togglePublish($quiz);

        $status = $quiz->is_published ? 'published' : 'unpublished';

        return redirect()
            ->back()
            ->with('success', "Quiz {$status} successfully.");
    }
}
