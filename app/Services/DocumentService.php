<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    /**
     * Get all documents belonging to the given user.
     */
    public function getDocumentsForUser(User $user): Collection
    {
        return $user->documents()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Upload a document, extract its text content, and save to the database.
     */
    public function uploadDocument(User $user, UploadedFile $file, string $title): Document
    {
        $fileType = strtolower($file->getClientOriginalExtension());
        $fileName = time() . '_' . uniqid() . '.' . $fileType;
        $filePath = $file->storeAs('documents', $fileName);

        $contentText = $this->extractContent(
            Storage::path($filePath),
            $fileType
        );

        return $user->documents()->create([
            'title' => $title,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'content_text' => $contentText,
        ]);
    }

    /**
     * Extract text content from a file based on its type.
     */
    public function extractContent(string $filePath, string $fileType): string
    {
        try {
            return match ($fileType) {
                'txt', 'text', 'md' => $this->extractFromText($filePath),
                'pdf' => $this->extractFromPdf($filePath),
                'docx' => $this->extractFromDocx($filePath),
                default => '',
            };
        } catch (\Throwable $e) {
            Log::warning('Failed to extract content from file.', [
                'file_path' => $filePath,
                'file_type' => $fileType,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * Delete a document's file from storage and remove its DB record.
     */
    public function deleteDocument(Document $document): bool
    {
        if ($document->file_path && Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        return (bool) $document->delete();
    }

    /**
     * Return the extracted text content of a document.
     */
    public function getDocumentContent(Document $document): string
    {
        return $document->content_text ?? '';
    }

    /**
     * Extract text from a plain text file.
     */
    private function extractFromText(string $filePath): string
    {
        $content = file_get_contents($filePath);

        return $content !== false ? $content : '';
    }

    /**
     * Extract text from a PDF file.
     *
     * Attempts shell-based pdftotext first, falls back to basic stream extraction.
     */
    private function extractFromPdf(string $filePath): string
    {
        // Attempt 1: Use pdftotext command-line tool (poppler-utils)
        $pdfToTextBinary = $this->findPdfToText();

        if ($pdfToTextBinary !== null) {
            $outputFile = tempnam(sys_get_temp_dir(), 'pdf_');

            $command = sprintf(
                '%s %s %s 2>&1',
                escapeshellarg($pdfToTextBinary),
                escapeshellarg($filePath),
                escapeshellarg($outputFile)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputFile)) {
                $content = file_get_contents($outputFile);
                @unlink($outputFile);

                if ($content !== false && trim($content) !== '') {
                    return trim($content);
                }
            }

            @unlink($outputFile);
        }

        // Attempt 2: Basic stream-based extraction
        return $this->extractPdfBasic($filePath);
    }

    /**
     * Find the pdftotext binary on the system.
     */
    private function findPdfToText(): ?string
    {
        $paths = ['pdftotext', '/usr/bin/pdftotext', '/usr/local/bin/pdftotext'];

        foreach ($paths as $path) {
            $command = sprintf('which %s 2>/dev/null || where %s 2>nul', escapeshellarg($path), escapeshellarg($path));
            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Basic PDF text extraction by reading the file stream.
     */
    private function extractPdfBasic(string $filePath): string
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            return '';
        }

        $text = '';

        // Extract text between stream/endstream markers
        if (preg_match_all('/stream\s*\n(.*?)\nendstream/s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                // Try to decompress zlib streams
                $decompressed = @gzuncompress($stream);
                if ($decompressed === false) {
                    $decompressed = @gzinflate($stream);
                }

                $source = $decompressed !== false ? $decompressed : $stream;

                // Extract text from Tj and TJ operators
                if (preg_match_all('/\((.*?)\)\s*Tj/s', $source, $textMatches)) {
                    $text .= implode(' ', $textMatches[1]);
                }
                if (preg_match_all('/\[(.*?)\]\s*TJ/s', $source, $tjMatches)) {
                    foreach ($tjMatches[1] as $tjContent) {
                        if (preg_match_all('/\((.*?)\)/', $tjContent, $innerMatches)) {
                            $text .= implode('', $innerMatches[1]);
                        }
                    }
                }
            }
        }

        // Clean up extracted text
        $text = preg_replace('/[^\P{C}\n\t]+/u', '', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * Extract text from a DOCX file using basic XML parsing.
     */
    private function extractFromDocx(string $filePath): string
    {
        $zip = new \ZipArchive();

        if ($zip->open($filePath) !== true) {
            return '';
        }

        $content = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($content === false) {
            return '';
        }

        // Remove XML tags but preserve paragraph breaks
        $content = str_replace('</w:p>', "\n", $content);
        $content = str_replace('</w:r>', ' ', $content);

        // Strip all XML tags
        $text = strip_tags($content);

        // Clean up whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
