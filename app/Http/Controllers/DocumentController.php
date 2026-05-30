<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UploadDocumentRequest;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for managing documents.
 *
 * Provides upload, listing, viewing, and deletion of documents.
 * Uses DocumentService for file handling and storage logic.
 */
class DocumentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected DocumentService $documentService,
    ) {}

    /**
     * Display a listing of documents for the authenticated user.
     */
    public function index(Request $request): View
    {
        $documents = Document::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('documents.index', compact('documents'));
    }

    /**
     * Show the form for uploading a new document.
     */
    public function create(): View
    {
        return view('documents.upload');
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(UploadDocumentRequest $request): RedirectResponse
    {
        $this->documentService->uploadDocument(
            $request->user(),
            $request->file('file'),
            $request->validated()['title'],
        );

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document): View
    {
        return view('documents.show', compact('document'));
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $this->documentService->deleteDocument($document);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }
}
