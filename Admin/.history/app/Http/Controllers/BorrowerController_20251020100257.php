<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BorrowerController extends Controller
{
    /** Display paginated + filtered borrowers */
    public function index(Request $request)
    {
        $filters = $request->all();

        $rows = Borrower::query()
            ->filter($filters)
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        // Pass pagination meta for your Blade metrics
        $meta = [
            'page'      => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
            'per_page'  => $rows->perPage(),
            'total'     => $rows->total(),
            'query'     => $request->query(),
        ];

        return view('borrower', compact('rows', 'meta'));
    }

    /** Store new borrower */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['status'] = $data['status'] ?? 'active';
        $data['is_archived'] = false;

        Borrower::create($data);

        return redirect()
            ->route('borrowers.index')
            ->with('success', 'Borrower created successfully.');
    }

    /** Update borrower */
    public function update(Request $request, Borrower $borrower)
    {
        $data = $this->validateData($request, $borrower->id);
        $borrower->update($data);

        return redirect()
            ->back()
            ->with('success', 'Borrower updated successfully.');
    }

    /** Archive borrower */
    public function archive(Borrower $borrower)
    {
        $borrower->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Borrower archived.');
    }

    /** Unarchive borrower */
    public function unarchive(Borrower $borrower)
    {
        $borrower->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        return redirect()->back()->with('success', 'Borrower unarchived.');
    }

    /** Update status */
    public function updateStatus(Request $request, Borrower $borrower)
    {
        $request->validate([
            'status' => ['required', Rule::in(Borrower::STATUSES)],
        ]);

        $borrower->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Borrower status updated.');
    }

    /** Soft delete */
    public function destroy(Borrower $borrower)
    {
        $borrower->delete();

        return redirect()->back()->with('success', 'Borrower deleted (soft).');
    }

    /** Force delete (permanent) */
    public function forceDestroy(int $id)
    {
        Borrower::withTrashed()->where('id', $id)->forceDelete();
        return redirect()->route('borrowers.index')->with('success', 'Borrower permanently deleted.');
    }

    /** Validation rules */
    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'full_name'      => ['required', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255', Rule::unique('borrowers', 'email')->ignore($id)],
            'phone'          => ['nullable', 'string', 'max:32'],
            'address'        => ['nullable', 'string', 'max:255'],
            'sex'            => ['nullable', Rule::in(['Male', 'Female', 'Prefer not to say'])],
            'occupation'     => ['nullable', 'string', 'max:255'],
            'birthday'       => ['nullable', 'date'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'civil_status'   => ['nullable', 'string', 'max:64'],
            'reference_no'   => ['nullable', 'string', 'max:128'],
            'status'         => ['nullable', Rule::in(Borrower::STATUSES)],
        ]);
    }
}
