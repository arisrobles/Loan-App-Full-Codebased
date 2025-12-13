<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Borrower;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    /**
     * Test endpoint to check if documents exist
     */
    public function test($id = null)
    {
        if ($id) {
            $doc = Document::find($id);
            return response()->json([
                'exists' => $doc !== null,
                'document' => $doc ? [
                    'id' => $doc->id,
                    'file_url' => $doc->file_url,
                    'file_name' => $doc->file_name,
                ] : null,
            ]);
        }
        return response()->json([
            'total' => Document::count(),
            'ids' => Document::pluck('id')->toArray(),
        ]);
    }

    /**
     * Display a listing of documents
     */
    public function index(Request $request)
    {
        $query = Document::with(['borrower', 'loan']);

        // Search filter
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhereHas('borrower', function ($b) use ($search) {
                      $b->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('document_type', $request->type);
        }

        // Status filter (for now, we'll use a simple active/archived based on existence)
        // You can add a status column later if needed
        $rows = $query->orderBy('uploaded_at', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);

        // Transform data to match view expectations while preserving pagination
        $transformed = $rows->getCollection()->map(function ($doc, $index) use ($rows) {
            return (object) [
                'id' => (int) $doc->id, // Ensure ID is an integer
                'title' => $doc->file_name,
                'type' => $this->mapDocumentType($doc->document_type),
                'owner' => $doc->borrower?->full_name ?? '—',
                'uploaded_at' => $doc->uploaded_at,
                'status' => 'active', // Default status
            ];
        });

        $rows->setCollection($transformed);

        // Calculate metadata
        $meta = [
            'total' => Document::count(),
            'active' => Document::count(), // All documents are active for now
            'archived' => 0,
            'expired' => 0,
        ];

        return view('documents', [
            'rows' => $rows,
            'meta' => $meta,
        ]);
    }

    /**
     * Store a newly uploaded document (to shared documents table)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_id' => ['required', 'exists:borrowers,id'],
            'loan_id' => ['nullable', 'exists:loans,id'],
            'document_type' => ['required', 'in:PRIMARY_ID,SECONDARY_ID,AGREEMENT,RECEIPT,SIGNATURE,PHOTO_2X2,OTHER'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // 5MB
        ]);

        // Verify borrower exists
        $borrower = Borrower::findOrFail($validated['borrower_id']);

        // If loan_id provided, verify loan belongs to borrower
        $loan = null;
        if ($validated['loan_id']) {
            $loan = Loan::where('id', $validated['loan_id'])
                        ->where('borrower_id', $borrower->id)
                        ->first();

            if (!$loan) {
                return back()->withErrors('Loan not found or does not belong to this borrower.');
            }

            // For RECEIPT documents, validate loan is disbursed (consistent with user backend)
            if ($validated['document_type'] === 'RECEIPT') {
                if ($loan->status !== Loan::ST_DISBURSED) {
                    return back()->withErrors("Cannot upload receipt. Loan status is '{$loan->status}'. Receipts can only be uploaded for disbursed loans.");
                }

                if (!$loan->is_active) {
                    return back()->withErrors('Cannot upload receipt. This loan is closed.');
                }
            }
        } else if ($validated['document_type'] === 'RECEIPT') {
            // RECEIPT must be linked to a loan (consistent with user backend)
            return back()->withErrors('Receipt must be linked to a loan. Please select a loan.');
        }

        $file = $request->file('file');

        // Store file in public/uploads directory (same as user backend)
        // Create uploads directory if it doesn't exist
        $uploadDir = public_path('uploads');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uniqueSuffix = time() . '-' . rand(100000000, 999999999);
        $extension = $file->getClientOriginalExtension();
        $filename = "file-{$uniqueSuffix}.{$extension}";
        $file->move($uploadDir, $filename);

        // File URL (accessible via /uploads/filename)
        $fileUrl = "/uploads/{$filename}";

        DB::beginTransaction();
        try {
            $document = Document::create([
                'borrower_id' => $borrower->id,
                'loan_id' => $validated['loan_id'] ? (int) $validated['loan_id'] : null,
                'document_type' => $validated['document_type'],
                'file_name' => $file->getClientOriginalName(),
                'file_url' => $fileUrl,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('documents.index')
                ->with('success', 'Document uploaded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // Delete uploaded file on error
            if (file_exists($uploadDir . '/' . $filename)) {
                unlink($uploadDir . '/' . $filename);
            }
            return back()->withErrors('Failed to upload document: ' . $e->getMessage());
        }
    }

    /**
     * Download a document
     */
    public function download($id)
    {
        try {
            $document = Document::findOrFail((int) $id);

            // Extract file path from file_url
            $fileUrl = $document->file_url ?? '';
            if (empty($fileUrl)) {
                abort(404, "Document has no file URL");
            }

            // Parse URL to get the relative path (e.g., /uploads/loan-documents/123/file.jpg or /uploads/file.jpg)
            $parsedUrl = parse_url($fileUrl, PHP_URL_PATH);
            if (!$parsedUrl) {
                // If parse_url fails, try to extract from the URL directly
                $parsedUrl = $fileUrl;
            }

            // Remove leading slash and get relative path
            $relativePath = ltrim($parsedUrl, '/');
            
            if (empty($relativePath)) {
                abort(404, "Could not extract file path from URL: {$fileUrl}");
            }

            // Try multiple possible locations where files might be stored
            // Files can be in Laravel public directory or User backend uploads directory
            $basePath = base_path();
            $possiblePaths = [
                // Laravel public directory (for files uploaded via admin panel)
                public_path($relativePath),
                // User backend uploads directory (for files uploaded via mobile app)
                $basePath . '/../User/backend/' . $relativePath,
                $basePath . '/User/backend/' . $relativePath,
                dirname($basePath) . '/User/backend/' . $relativePath,
                // Alternative paths
                storage_path('app/public/' . $relativePath),
                storage_path('app/' . $relativePath),
            ];

            $filePath = null;
            foreach ($possiblePaths as $path) {
                // Normalize path separators for Windows
                $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
                
                // Resolve relative paths (.. and .)
                $resolvedPath = realpath($normalizedPath);
                
                // If realpath fails, try the path as-is (might be a new file)
                if ($resolvedPath === false) {
                    $resolvedPath = $normalizedPath;
                }
                
                if ($resolvedPath && file_exists($resolvedPath) && is_file($resolvedPath)) {
                    $filePath = $resolvedPath;
                    break;
                }
            }

            if (!$filePath) {
                \Log::error('Document file not found', [
                    'document_id' => $id,
                    'file_url' => $fileUrl,
                    'relative_path' => $relativePath,
                    'base_path' => $basePath,
                    'tried_paths' => $possiblePaths,
                ]);
                abort(404, "File not found: {$relativePath}. Checked multiple locations.");
            }

            $mimeType = $document->mime_type ?? 'application/octet-stream';
            $originalName = $document->file_name ?? basename($relativePath);

            return response()->download($filePath, $originalName, [
                'Content-Type' => $mimeType,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, "Document not found");
        } catch (\Exception $e) {
            \Log::error('Error downloading document', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, "Error downloading document: " . $e->getMessage());
        }
    }

    /**
     * View a document (inline)
     */
    public function view($id)
    {
        try {
            $document = Document::findOrFail((int) $id);

            // Extract file path from file_url
            $fileUrl = $document->file_url ?? '';
            if (empty($fileUrl)) {
                abort(404, "Document has no file URL");
            }

            // Parse URL to get the relative path (e.g., /uploads/loan-documents/123/file.jpg or /uploads/file.jpg)
            $parsedUrl = parse_url($fileUrl, PHP_URL_PATH);
            if (!$parsedUrl) {
                // If parse_url fails, try to extract from the URL directly
                $parsedUrl = $fileUrl;
            }

            // Remove leading slash and get relative path
            $relativePath = ltrim($parsedUrl, '/');
            
            if (empty($relativePath)) {
                abort(404, "Could not extract file path from URL: {$fileUrl}");
            }

            // Try multiple possible locations where files might be stored
            // Files can be in Laravel public directory or User backend uploads directory
            $basePath = base_path();
            $possiblePaths = [
                // Laravel public directory (for files uploaded via admin panel)
                public_path($relativePath),
                // User backend uploads directory (for files uploaded via mobile app)
                $basePath . '/../User/backend/' . $relativePath,
                $basePath . '/User/backend/' . $relativePath,
                dirname($basePath) . '/User/backend/' . $relativePath,
                // Alternative paths
                storage_path('app/public/' . $relativePath),
                storage_path('app/' . $relativePath),
            ];

            $filePath = null;
            foreach ($possiblePaths as $path) {
                // Normalize path separators for Windows
                $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
                
                // Resolve relative paths (.. and .)
                $resolvedPath = realpath($normalizedPath);
                
                // If realpath fails, try the path as-is (might be a new file)
                if ($resolvedPath === false) {
                    $resolvedPath = $normalizedPath;
                }
                
                if ($resolvedPath && file_exists($resolvedPath) && is_file($resolvedPath)) {
                    $filePath = $resolvedPath;
                    break;
                }
            }

            if (!$filePath) {
                \Log::error('Document file not found for view', [
                    'document_id' => $id,
                    'file_url' => $fileUrl,
                    'relative_path' => $relativePath,
                    'base_path' => $basePath,
                    'tried_paths' => $possiblePaths,
                ]);
                abort(404, "File not found: {$relativePath}. Checked multiple locations.");
            }

            $mimeType = $document->mime_type ?? 'application/octet-stream';

            return response()->file($filePath, [
                'Content-Type' => $mimeType,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, "Document not found");
        } catch (\Exception $e) {
            \Log::error('Error viewing document', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, "Error viewing document: " . $e->getMessage());
        }
    }

    /**
     * Delete a document
     */
    public function destroy($id)
    {
        $document = Document::find($id);

        if (!$document) {
            abort(404, 'Document not found.');
        }

        // Extract filename from URL
        $filename = basename($document->file_url);
        $filePath = public_path('uploads/' . $filename);

        // Delete file if exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Map document type enum to display name
     */
    private function mapDocumentType(string $type): string
    {
        $map = [
            'PRIMARY_ID' => 'ID',
            'SECONDARY_ID' => 'Secondary ID',
            'AGREEMENT' => 'Contract',
            'RECEIPT' => 'Receipt',
            'SIGNATURE' => 'Signature',
            'PHOTO_2X2' => '2x2 Photo',
            'OTHER' => 'Misc',
        ];

        return $map[$type] ?? $type;
    }
}

