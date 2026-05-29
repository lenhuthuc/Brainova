<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GenerateQuestionsRequest;
use App\Models\Document;
use App\Models\Quiz;
use App\Services\AIGeneratorService;
use App\Services\QuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for AI-powered quiz question generation.
 *
 * Handles the workflow of generating questions from documents using AI,
 * previewing the generated questions, and saving selected ones to a quiz.
 */
class AIGeneratorController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AIGeneratorService $aiGeneratorService,
        protected QuestionService $questionService,
    ) {}

    /**
     * Show the AI question generation form.
     */
    public function showForm(Request $request): View
    {
        $user = $request->user();
        $documents = Document::where('user_id', $user->id)->latest()->get();
        $quizzes = Quiz::where('user_id', $user->id)->latest()->get();

        return view('ai.generate', compact('documents', 'quizzes'));
    }

    /**
     * Generate questions using AI and show preview.
     */
    public function generate(GenerateQuestionsRequest $request): View
    {
        $validated = $request->validated();

        $document = Document::findOrFail($validated['document_id']);
        $quiz = Quiz::findOrFail($validated['quiz_id']);

        $generatedQuestions = $this->aiGeneratorService->generateQuestions(
            content: $document->content_text,
            numberOfQuestions: (int) $validated['number_of_questions'],
            questionType: $validated['question_type'],
            difficulty: $validated['difficulty'],
        );

        // Store generated questions in session for the confirmation step
        session(['generated_questions' => $generatedQuestions, 'target_quiz_id' => $quiz->id]);

        $documents = Document::where('user_id', $request->user()->id)->latest()->get();
        $quizzes = Quiz::where('user_id', $request->user()->id)->latest()->get();

        return view('ai.generate', compact(
            'documents',
            'quizzes',
            'generatedQuestions',
            'quiz',
        ));
    }

    /**
     * Confirm and save selected generated questions to the quiz.
     */
    public function confirmAndSave(Request $request): RedirectResponse
    {
        $generatedQuestions = $request->input('questions', session('generated_questions', []));
        $quizId = $request->input('quiz_id', session('target_quiz_id'));

        if (empty($generatedQuestions) || empty($quizId)) {
            return redirect()
                ->route('ai.generate.form')
                ->with('error', 'No questions to save. Please generate questions first.');
        }

        $quiz = Quiz::findOrFail($quizId);

        // Get selected question indices (if provided), otherwise save all
        $selectedIndices = $request->input('selected', array_keys($generatedQuestions));

        foreach ($selectedIndices as $index) {
            if (isset($generatedQuestions[$index])) {
                $this->questionService->createWithAnswers($quiz, $generatedQuestions[$index]);
            }
        }

        // Clear session data
        session()->forget(['generated_questions', 'target_quiz_id']);

        return redirect()
            ->route('quizzes.show', $quiz)
            ->with('success', count($selectedIndices) . ' question(s) saved successfully.');
    }
}
