<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\LoanDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LoanDocumentController extends Controller
{
    /**
     * Display all documents for a borrower (with upload form).
     */
    public function index(Borrower $borrower)
    {
        // All documents for this borrower
        $documents = LoanDocument::with(['loan', 'uploader'])
            ->where('borrower_id', $borrower->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Optional: list of this borrower's loans for the dropdown
        $loans = Loan::where('borrower_id', $borrower->id)
            ->orderBy('application_date', 'desc')
            ->get();

        return view('loan_documents.index', [
            'borrower'  => $borrower,
            'loans'     => $loans,
            'documents' => $documents,
        ]);
    }

    /**
     * Store a newly uploaded document for a borrower.
     */
    public function store(Request $request, Borrower $borrower)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:valid_id,loan_agreement,proof_of_income,collateral_document,other',
            'loan_id'       => 'nullable|exists:loans,id',
            'document_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
            'remarks'       => 'nullable|string|max:255',
        ]);

        $file = $validated['document_file'];

        // Store file under storage/app/loan-documents
        $path = $file->store('loan-documents');

        $document = LoanDocument::create([
            'borrower_id'          => $borrower->id,
            'loan_id'              => $validated['loan_id'] ?? null,
            'uploaded_by_user_id'  => Auth::id(), // null if not logged in
            'document_type'        => $validated['document_type'],
            'original_name'        => $file->getClientOriginalName(),
            'file_path'            => $path,
            'mime_type'            => $file->getClientMimeType(),
            'remarks'              => $validated['remarks'] ?? null,
        ]);

        return redirect()
            ->route('borrowers.documents.index', $borrower)
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Download a document.
     */
    public function download(LoanDocument $loanDocument)
    {
        // TODO: add authorization if needed (role/admin check)
        if (! Storage::exists($loanDocument->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::download(
            $loanDocument->file_path,
            $loanDocument->original_name
        );
    }

    /**
     * Remove the specified document.
     */
    public function destroy(LoanDocument $loanDocument)
    {
        // TODO: add authorization if needed (only admin/officer)
        if (Storage::exists($loanDocument->file_path)) {
            Storage::delete($loanDocument->file_path);
        }

        $borrowerId = $loanDocument->borrower_id;

        $loanDocument->delete();

        return redirect()
            ->route('borrowers.documents.index', $borrowerId)
            ->with('success', 'Document deleted successfully.');
    }
}
