<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BorrowerController extends Controller
{
    /** Display paginated + filtered borrowers */
    public function index(Request $request)
    {
        $filters = $request->all();

        $rows = Borrower::query()
            ->filter($filters) // assumes scopeFilter() exists on Borrower
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        // Pass pagination meta for Blade metrics
        $meta = [
            'page'      => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
            'per_page'  => $rows->perPage(),
            'total'     => $rows->total(),
            'query'     => $request->query(),
        ];

        return view('borrowers.index', compact('rows', 'meta'));
    }

    /** CSV export for filtered borrowers */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->all();

        $query = Borrower::query()->filter($filters);

        $fileName = 'borrowers-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $columns = [
            'ID',
            'Full Name',
            'Email',
            'Phone',
            'Address',
            'Sex',
            'Occupation',
            'Birthday',
            'Monthly Income',
            'Civil Status',
            'Reference No',
            'Status',
            'Is Archived',
            'Created At',
            'Updated At',
        ];

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($borrowers) use ($handle) {
                foreach ($borrowers as $b) {
                    fputcsv($handle, [
                        $b->id,
                        $b->full_name,
                        $b->email,
                        $b->phone,
                        $b->address,
                        $b->sex,
                        $b->occupation,
                        optional($b->birthday)->format('Y-m-d'),
                        $b->monthly_income,
                        $b->civil_status,
                        $b->reference_no,
                        $b->status,
                        $b->is_archived ? 1 : 0,
                        optional($b->created_at)->format('Y-m-d H:i:s'),
                        optional($b->updated_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Show borrower profile + loan history */
    public function show(Borrower $borrower)
    {
        // Eager load loans with repayments and payments
        $borrower->load([
            'loans' => function($query) {
                $query->with(['repayments', 'payments' => function($q) {
                    $q->with('receiptDocument')->orderBy('paid_at', 'desc');
                }])->orderBy('created_at', 'desc');
            }
        ]);

        // Calculate borrower statistics
        $loans = $borrower->loans;
        $totalLoans = $loans->count();
        $totalBorrowed = $loans->sum('principal_amount');
        $totalDisbursed = $loans->sum('total_disbursed');
        $totalPaid = $loans->sum('total_paid');
        $totalPenalties = $loans->sum('total_penalties');

        // Calculate outstanding balance (sum of all outstanding repayments)
        $totalOutstanding = 0;
        foreach ($loans as $loan) {
            foreach ($loan->repayments as $repayment) {
                $due = (float)$repayment->amount_due;
                $paid = (float)$repayment->amount_paid;
                $penalty = (float)$repayment->penalty_applied;
                $outstanding = max(0, $due + $penalty - $paid);
                $totalOutstanding += $outstanding;
            }
        }

        // Get all payments for this borrower
        $payments = \App\Models\Payment::where('borrower_id', $borrower->id)
            ->with(['loan', 'repayment', 'receiptDocument', 'approvedBy'])
            ->orderBy('paid_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Payment statistics
        $paymentStats = [
            'total' => $payments->total(),
            'approved' => \App\Models\Payment::where('borrower_id', $borrower->id)
                ->where('status', \App\Models\Payment::STATUS_APPROVED)
                ->sum('amount'),
            'pending' => \App\Models\Payment::where('borrower_id', $borrower->id)
                ->where('status', \App\Models\Payment::STATUS_PENDING)
                ->sum('amount'),
            'rejected' => \App\Models\Payment::where('borrower_id', $borrower->id)
                ->where('status', \App\Models\Payment::STATUS_REJECTED)
                ->sum('amount'),
        ];

        return view('borrowers.show', compact(
            'borrower',
            'loans',
            'payments',
            'totalLoans',
            'totalBorrowed',
            'totalDisbursed',
            'totalPaid',
            'totalPenalties',
            'totalOutstanding',
            'paymentStats'
        ));
    }

    /** Edit borrower form */
    public function edit(Borrower $borrower)
    {
        return view('borrowers.edit', compact('borrower'));
    }

    /** Store new borrower */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['status']      = $data['status'] ?? 'active';
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
            ->route('borrowers.show', $borrower)
            ->with('success', 'Borrower updated successfully.');
    }

    /** Archive borrower */
    public function archive(Borrower $borrower)
    {
        $borrower->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Borrower archived.');
    }

    /** Unarchive borrower */
    public function unarchive(Borrower $borrower)
    {
        $borrower->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Borrower unarchived.');
    }

    /** Update status only */
    public function updateStatus(Request $request, Borrower $borrower)
    {
        $request->validate([
            'status' => ['required', Rule::in(Borrower::STATUSES)],
        ]);

        $borrower->update(['status' => $request->status]);

        return redirect()
            ->back()
            ->with('success', 'Borrower status updated.');
    }

    /** Soft delete */
    public function destroy(Borrower $borrower)
    {
        $borrower->delete();

        return redirect()
            ->route('borrowers.index')
            ->with('success', 'Borrower deleted (soft).');
    }

    /** Force delete (permanent) */
    public function forceDestroy(Borrower $borrower)
    {
        $borrower->forceDelete();

        return redirect()
            ->route('borrowers.index')
            ->with('success', 'Borrower permanently deleted.');
    }

    /** Common validation rules */
    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'full_name'      => ['required', 'string', 'max:255'],
            'email'          => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('borrowers', 'email')->ignore($id),
            ],
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
