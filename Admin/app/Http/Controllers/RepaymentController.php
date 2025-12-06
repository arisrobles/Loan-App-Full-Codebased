<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Repayment;
use Illuminate\Http\Request;

class RepaymentController extends Controller
{
    /**
     * List all repayments for a loan.
     * (You can add a route for this later if you want a "Repayment Schedule" page.)
     */
    public function index(Loan $loan)
    {
        $repayments = $loan->repayments()
            ->with(['payments' => function($query) {
                $query->with(['receiptDocument', 'approvedBy'])
                      ->orderBy('paid_at', 'desc')
                      ->orderBy('created_at', 'desc');
            }])
            ->orderBy('due_date')
            ->get();

        // Group payments by repayment_id for easier display
        $paymentsByRepayment = \App\Models\Payment::where('loan_id', $loan->id)
            ->with(['receiptDocument', 'approvedBy'])
            ->get()
            ->groupBy('repayment_id');

        return view('repayments.index', compact('loan', 'repayments', 'paymentsByRepayment'));
    }

    /**
     * Show "create repayment" form for a specific loan.
     */
    public function create(Loan $loan)
    {
        $repayment = new Repayment();

        return view('repayments.create', compact('loan', 'repayment'));
    }

    /**
     * Store a new repayment row for a loan.
     */
    public function store(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'due_date'    => ['required', 'date'],
            'amount_due'  => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'remarks'     => ['nullable', 'string', 'max:255'],
        ]);

        $data['loan_id'] = $loan->id;

        Repayment::create($data);

        return redirect()
            ->route('loans.index') // or a dedicated route like route('repayments.index', $loan)
            ->with('success', 'Repayment schedule added.');
    }

    /**
     * Edit a repayment row.
     */
    public function edit(Loan $loan, Repayment $repayment)
    {
        // Optional safety: ensure this repayment belongs to this loan
        if ($repayment->loan_id !== $loan->id) {
            abort(404);
        }

        return view('repayments.edit', compact('loan', 'repayment'));
    }

    /**
     * Update repayment details (due date / amounts / remarks).
     */
    public function update(Request $request, Loan $loan, Repayment $repayment)
    {
        if ($repayment->loan_id !== $loan->id) {
            abort(404);
        }

        $data = $request->validate([
            'due_date'    => ['required', 'date'],
            'amount_due'  => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'remarks'     => ['nullable', 'string', 'max:255'],
        ]);

        $repayment->update($data);

        return redirect()
            ->route('loans.index') // or route('repayments.index', $loan)
            ->with('success', 'Repayment updated.');
    }

    /**
     * Optional: delete a repayment row.
     */
    public function destroy(Loan $loan, Repayment $repayment)
    {
        if ($repayment->loan_id !== $loan->id) {
            abort(404);
        }

        $repayment->delete();

        return redirect()
            ->route('loans.index') // or route('repayments.index', $loan)
            ->with('success', 'Repayment removed.');
    }

    /**
     * Apply / recompute penalty for a single repayment.
     *
     * Route:
     *   POST /repayments/{repayment}/apply-penalty
     *   -> name: repayments.applyPenalty
     */
    public function applyPenalty(Request $request, Repayment $repayment)
    {
        $data = $request->validate([
            'daily_rate' => ['nullable', 'numeric', 'min:0'], // override Loan.penalty_daily_rate
            'grace_days' => ['nullable', 'integer', 'min:0'], // override Loan.penalty_grace_days
        ]);

        $dailyRate = $data['daily_rate'] ?? null;
        $graceDays = $data['grace_days'] ?? null;

        // Use your model helper
        $penalty = $repayment->computePenalty(
            overrideDailyRate: $dailyRate,
            overrideGraceDays: $graceDays
        );

        // Save penalty_applied to DB (column exists in your SQL)
        $repayment->penalty_applied = $penalty;
        $repayment->save();

        // Optional: update loan.total_penalties based on all repayments
        if ($repayment->loan) {
            $loan = $repayment->loan;
            $loan->total_penalties = $loan->repayments()->sum('penalty_applied');
            $loan->save();
        }

        return redirect()
            ->back()
            ->with('success', 'Penalty updated to ₱' . number_format((float)$penalty, 2));
    }
}