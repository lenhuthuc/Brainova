<?php

use App\Http\Controllers\AIGeneratorController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RAGController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Quiz CRUD
    Route::resource('quizzes', QuizController::class);
    Route::post('quizzes/{quiz}/toggle-publish', [QuizController::class, 'togglePublish'])
        ->name('quizzes.toggle-publish');

    // Questions (nested under quiz)
    Route::resource('quizzes.questions', QuestionController::class)
        ->except(['index', 'show']);

    // Documents
    Route::resource('documents', DocumentController::class)
        ->except(['edit', 'update']);

    // AI Generator (teacher only)
    Route::prefix('ai')->name('ai.')->middleware('role:teacher')->group(function () {
        Route::get('generate', [AIGeneratorController::class, 'showForm'])->name('generate.form');
        Route::post('generate', [AIGeneratorController::class, 'generate'])->name('generate');
        Route::post('generate/save', [AIGeneratorController::class, 'confirmAndSave'])->name('generate.save');
    });

    // Attempts
    Route::prefix('attempts')->name('attempts.')->group(function () {
        Route::get('available', [AttemptController::class, 'available'])->name('available');
        Route::post('{quiz}/start', [AttemptController::class, 'start'])->name('start');
        Route::get('{attempt}/take', [AttemptController::class, 'take'])->name('take');
        Route::post('{attempt}/submit', [AttemptController::class, 'submit'])->name('submit');
        Route::get('{attempt}/result', [AttemptController::class, 'result'])->name('result');
        Route::get('history', [AttemptController::class, 'history'])->name('history');
    });

    // RAG Chat
    Route::prefix('attempts/{attempt}/rag')->name('rag.')->group(function () {
        Route::get('chat', [RAGController::class, 'chat'])->name('chat');
        Route::post('ask', [RAGController::class, 'ask'])->name('ask');
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
