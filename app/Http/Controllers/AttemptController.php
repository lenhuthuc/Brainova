<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SubmitAttemptRequest;
use App\Models\Attempt;
use App\Models\Quiz;
use App\Services\AttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for managing quiz attempts.
 *
 * Handles the full attempt lifecycle: browsing available quizzes,
 * starting an attempt, taking the quiz, submitting answers,
 * viewing results, and browsing attempt history.
 */
class AttemptController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AttemptService $attemptService,
    ) {}

    /**
     * Display published quizzes available for taking.
     */
    public function available(): View
    {
        $quizzes = Quiz::where('is_published', true)
            ->withCount('questions')
            ->latest()
            ->paginate(15);

        return view('attempts.available', compact('quizzes'));
    }

    /**
     * Start a new attempt for the given quiz.
     */
    public function start(Request $request, Quiz $quiz): RedirectResponse
    {
        $user = $request->user();

        // Check if user already has an active (incomplete) attempt for this quiz
        $activeAttempt = Attempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->whereNull('completed_at')
            ->first();

        if ($activeAttempt) {
            return redirect()
                ->route('attempts.take', $activeAttempt)
                ->with('info', 'You already have an active attempt for this quiz.');
        }

        $attempt = $this->attemptService->startAttempt($user, $quiz);

        return redirect()
            ->route('attempts.take', $attempt)
            ->with('success', 'Quiz attempt started. Good luck!');
    }

    /**
     * Display the quiz-taking interface for an active attempt.
     */
    public function take(Request $request, Attempt $attempt): View
    {
        // Ensure the attempt belongs to the authenticated user
        if ($attempt->user_id !== $request->user()->id) {
            abort(403, 'This attempt does not belong to you.');
        }

        // Ensure the attempt has not been completed
        if ($attempt->completed_at !== null) {
            return redirect()
                ->route('attempts.result', $attempt)
                ->with('info', 'This attempt has already been completed.');
        }

        $attempt->load('quiz.questions.answers');
        $quiz = $attempt->quiz;
        return view('attempts.take', compact('attempt', 'quiz'));
    }

    /**
     * Submit answers for the given attempt.
     */
    public function submit(SubmitAttemptRequest $request, Attempt $attempt): RedirectResponse
    {
        $this->attemptService->submitAttempt($attempt, $request->validated()['responses']);

        return redirect()
            ->route('attempts.result', $attempt)
            ->with('success', 'Quiz submitted successfully!');
    }

    /**
     * Display the result of a completed attempt.
     */
    public function result(Attempt $attempt): View
    {
        $attempt = $this->attemptService->getAttemptWithDetails($attempt);

        return view('attempts.result', compact('attempt'));
    }

    /**
     * Display the authenticated user's attempt history.
     */
    public function history(Request $request): View
    {
        $attempts = $this->attemptService->getHistoryForUser($request->user());

        return view('attempts.history', compact('attempts'));
    }
}
