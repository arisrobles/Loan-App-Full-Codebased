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
            'document_type' => ['required', 'in:PRIMARY_ID,SECONDARY_ID,AGREEMENT,RECEIPT,OTHER'],
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

            // Extract filename from file_url
            $fileUrl = $document->file_url ?? '';
            if (empty($fileUrl)) {
                abort(404, "Document has no file URL");
            }

            // Parse URL to get filename
            $parsedUrl = parse_url($fileUrl, PHP_URL_PATH);
            if ($parsedUrl) {
                $filename = basename($parsedUrl);
            } else {
                // If parse_url fails, try to extract from the URL directly
                $filename = basename($fileUrl);
            }

            if (empty($filename)) {
                abort(404, "Could not extract filename from URL: {$fileUrl}");
            }

            // Try multiple possible locations where files might be stored
            // Files can be in Laravel public/uploads or User backend uploads directory
            $basePath = base_path();
            $possiblePaths = [
                public_path('uploads/' . $filename),
                $basePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'User' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename,
                dirname($basePath) . DIRECTORY_SEPARATOR . 'User' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename,
                storage_path('app/public/uploads/' . $filename),
                storage_path('app/uploads/' . $filename),
            ];

            $filePath = null;
            foreach ($possiblePaths as $path) {
                // Resolve relative paths
                $resolvedPath = realpath($path);
                if ($resolvedPath && file_exists($resolvedPath) && is_file($resolvedPath)) {
                    $filePath = $resolvedPath;
                    break;
                }
                // Also try without realpath in case of symlinks
                if (file_exists($path) && is_file($path)) {
                    $filePath = $path;
                    break;
                }
            }

            if (!$filePath) {
                \Log::error('Document file not found', [
                    'document_id' => $id,
                    'file_url' => $fileUrl,
                    'filename' => $filename,
                    'base_path' => $basePath,
                    'tried_paths' => $possiblePaths,
                ]);
                abort(404, "File not found: {$filename}. Checked multiple locations.");
            }

            $mimeType = $document->mime_type ?? 'application/octet-stream';
            $originalName = $document->file_name ?? $filename;

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

            // Extract filename from file_url
            $fileUrl = $document->file_url ?? '';
            if (empty($fileUrl)) {
                abort(404, "Document has no file URL");
            }

            // Parse URL to get filename
            $parsedUrl = parse_url($fileUrl, PHP_URL_PATH);
            if ($parsedUrl) {
                $filename = basename($parsedUrl);
            } else {
                // If parse_url fails, try to extract from the URL directly
                $filename = basename($fileUrl);
            }

            if (empty($filename)) {
                abort(404, "Could not extract filename from URL: {$fileUrl}");
            }

            // Try multiple possible locations where files might be stored
            // Files can be in Laravel public/uploads or User backend uploads directory
            $basePath = base_path();
            $possiblePaths = [
                public_path('uploads/' . $filename),
                $basePath . '/../User/backend/uploads/' . $filename,
                $basePath . '/User/backend/uploads/' . $filename,
                dirname($basePath) . '/User/backend/uploads/' . $filename,
                storage_path('app/public/uploads/' . $filename),
                storage_path('app/uploads/' . $filename),
            ];

            // Normalize paths (resolve .. and .)
            $possiblePaths = array_map(function($path) {
                return realpath(dirname($path)) . '/' . basename($path);
            }, $possiblePaths);

            $filePath = null;
            foreach ($possiblePaths as $path) {
                if ($path && file_exists($path) && is_file($path)) {
                    $filePath = $path;
                    break;
                }
            }

            if (!$filePath) {
                // Try one more time with direct path resolution
                $userBackendPath = dirname(dirname($basePath)) . '/User/backend/uploads/' . $filename;
                if (file_exists($userBackendPath) && is_file($userBackendPath)) {
                    $filePath = $userBackendPath;
                }
            }

            if (!$filePath) {
                \Log::error('Document file not found for view', [
                    'document_id' => $id,
                    'file_url' => $fileUrl,
                    'filename' => $filename,
                    'base_path' => $basePath,
                    'tried_paths' => $possiblePaths,
                ]);
                abort(404, "File not found: {$filename}. Checked multiple locations.");
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
            'OTHER' => 'Misc',
        ];

        return $map[$type] ?? $type;
    }
}

