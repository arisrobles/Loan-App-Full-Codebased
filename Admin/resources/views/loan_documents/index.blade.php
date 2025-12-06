{{-- resources/views/loan_documents/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Documents – {{ $borrower->full_name }} – MasterFunds</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 text-slate-50">
<div class="max-w-5xl mx-auto py-10 px-4">

    {{-- Header --}}
    <div class="mb-8">
        <p class="text-xs uppercase tracking-[0.25em] text-blue-400/70 mb-2">
            Borrower Documents
        </p>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight mb-2">
            {{ $borrower->full_name }}
        </h1>
        <p class="text-sm text-slate-400">
            Reference: {{ $borrower->reference_no ?? 'N/A' }} • Status: {{ ucfirst($borrower->status) }}
        </p>
    </div>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 text-emerald-200 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-500/40 bg-red-500/10 text-red-200 px-4 py-3 text-sm">
            <p class="font-semibold mb-1">There were some problems with your input:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Upload Form --}}
    <div class="mb-10 border border-slate-800 rounded-2xl bg-slate-900/40 p-6">
        <h2 class="text-lg font-semibold mb-4 text-slate-100">
            Upload New Document
        </h2>

        <form action="{{ route('borrowers.documents.store', $borrower) }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-4">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                {{-- Document Type --}}
                <div>
                    <label class="block text-sm font-medium text-slate-200 mb-1">
                        Document Type
                    </label>
                    <select name="document_type"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="valid_id" {{ old('document_type') === 'valid_id' ? 'selected' : '' }}>Valid ID</option>
                        <option value="loan_agreement" {{ old('document_type') === 'loan_agreement' ? 'selected' : '' }}>Loan Agreement</option>
                        <option value="proof_of_income" {{ old('document_type') === 'proof_of_income' ? 'selected' : '' }}>Proof of Income</option>
                        <option value="collateral_document" {{ old('document_type') === 'collateral_document' ? 'selected' : '' }}>Collateral Document</option>
                        <option value="other" {{ old('document_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                {{-- Related Loan (optional) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-200 mb-1">
                        Related Loan (optional)
                    </label>
                    <select name="loan_id"
                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="">None / General</option>
                        @foreach($loans as $loan)
                            <option value="{{ $loan->id }}"
                                {{ (string) old('loan_id') === (string) $loan->id ? 'selected' : '' }}>
                                {{ $loan->reference }} — ₱{{ number_format($loan->principal_amount, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- File --}}
            <div>
                <label class="block text-sm font-medium text-slate-200 mb-1">
                    Document File (JPG, PNG, PDF)
                </label>
                <input type="file"
                       name="document_file"
                       class="w-full text-sm text-slate-200 bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 file:bg-slate-800 file:border-none file:mr-3 file:px-3 file:py-2 file:rounded-md file:text-xs file:text-slate-100">
            </div>

            {{-- Remarks --}}
            <div>
                <label class="block text-sm font-medium text-slate-200 mb-1">
                    Remarks (optional)
                </label>
                <textarea name="remarks"
                          rows="2"
                          class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('remarks') }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500 shadow-sm shadow-blue-500/40">
                    Upload Document
                </button>
            </div>
        </form>
    </div>

    {{-- Existing Documents --}}
    <div>
        <h2 class="text-lg font-semibold mb-3 text-slate-100">
            Existing Documents
        </h2>

        @if($documents->isEmpty())
            <div class="border border-slate-800 rounded-2xl bg-slate-900/40 px-4 py-6 text-center text-sm text-slate-400">
                No documents uploaded for this borrower yet.
            </div>
        @else
            <div class="overflow-x-auto border border-slate-800 rounded-2xl bg-slate-900/40">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-900/70 text-slate-300">
                    <tr>
                        <th class="px-3 py-2 text-left">Type</th>
                        <th class="px-3 py-2 text-left">File</th>
                        <th class="px-3 py-2 text-left">Loan</th>
                        <th class="px-3 py-2 text-left">Uploaded By</th>
                        <th class="px-3 py-2 text-left">Remarks</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($documents as $doc)
                        <tr class="border-t border-slate-800/80">
                            <td class="px-3 py-2 align-top text-slate-100">
                                {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                            </td>
                            <td class="px-3 py-2 align-top text-slate-200">
                                {{ $doc->original_name }}
                            </td>
                            <td class="px-3 py-2 align-top text-slate-200">
                                @if($doc->loan)
                                    {{ $doc->loan->reference }}
                                @else
                                    <span class="text-slate-500 text-xs">None</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 align-top text-slate-200">
                                @if($doc->uploader)
                                    {{ $doc->uploader->username }}
                                @else
                                    <span class="text-slate-500 text-xs">System / Unknown</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 align-top text-slate-200">
                                {{ $doc->remarks }}
                            </td>
                            <td class="px-3 py-2 align-top text-right space-x-2">
                                <a href="{{ route('documents.download', $doc) }}"
                                   class="inline-block text-blue-400 hover:text-blue-300">
                                    Download
                                </a>

                                <form action="{{ route('documents.destroy', $doc) }}"
                                      method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('Delete this document?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-400 hover:text-red-300">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
</body>
</html>
