<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Document;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for the dashboard page.
 *
 * Displays role-specific statistics and recent activity
 * for both teachers and students.
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard with role-specific data.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->role === 'teacher') {
            $quizCount = Quiz::where('user_id', $user->id)->count();
            $questionCount = Question::whereHas('quiz', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count();
            $documentCount = Document::where('user_id', $user->id)->count();
            $recentAttempts = Attempt::whereHas('quiz', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->with(['user', 'quiz'])
                ->latest()
                ->take(10)
                ->get();

            return view('dashboard.index', compact(
                'quizCount',
                'questionCount',
                'documentCount',
                'recentAttempts',
            ));
        }

        // Student dashboard
        $availableQuizzes = Quiz::where('is_published', true)
            ->withCount('questions')
            ->latest()
            ->get();
        $recentAttempts = Attempt::where('user_id', $user->id)
            ->with('quiz')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.index', compact(
            'availableQuizzes',
            'recentAttempts',
        ));
    }
}
