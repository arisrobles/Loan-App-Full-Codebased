<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Repayment;
use App\Models\Payment;
use App\Models\Borrower;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonImmutable;

class PaymentController extends Controller
{
    /**
     * List payments - comprehensive payment history with filters
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all'); // Changed default to 'all'

        $query = Payment::with(['loan', 'borrower', 'repayment', 'receiptDocument', 'approvedBy']);

        // Filter by status (if not 'all')
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by loan
        if ($request->filled('loan_id')) {
            $query->where('loan_id', $request->loan_id);
        }

        // Filter by borrower
        if ($request->filled('borrower_id')) {
            $query->where('borrower_id', $request->borrower_id);
        }

        // Search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->whereHas('loan', function($loan) use ($search) {
                    $loan->where('reference', 'like', "%{$search}%");
                })
                ->orWhereHas('borrower', function($borrower) use ($search) {
                    $borrower->where('full_name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('paid_at', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        // Calculate statistics
        $stats = [
            'total' => Payment::count(),
            'approved' => Payment::where('status', Payment::STATUS_APPROVED)->count(),
            'pending' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'rejected' => Payment::where('status', Payment::STATUS_REJECTED)->count(),
            'total_amount' => Payment::where('status', Payment::STATUS_APPROVED)->sum('amount'),
            'pending_amount' => Payment::where('status', Payment::STATUS_PENDING)->sum('amount'),
            'total_penalties' => Payment::where('status', Payment::STATUS_APPROVED)->sum('penalty_amount'),
        ];

        // Get borrowers for filter dropdown
        $borrowers = Borrower::orderBy('full_name')->get();

        return view('payments.index', [
            'payments' => $payments,
            'status' => $status,
            'stats' => $stats,
            'borrowers' => $borrowers,
        ]);
    }

    /**
     * Approve a pending payment
     */
    public function approve(Request $request, Payment $payment)
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return back()->withErrors('This payment is not pending approval.');
        }

        DB::beginTransaction();
        try {
            $loan = $payment->loan;

            // Validate loan is still disbursed and active
            if ($loan->status !== Loan::ST_DISBURSED) {
                return back()->withErrors("Cannot approve payment. Loan status is '{$loan->status}'. Only disbursed loans can receive payments.");
            }

            if (!$loan->is_active) {
                return back()->withErrors('Cannot approve payment. This loan is closed.');
            }

            $amount = (float) $payment->amount;
            $penaltyAmount = (float) $payment->penalty_amount;
            $today = CarbonImmutable::now('Asia/Manila')->startOfDay();

            // Update repayment if specified
            if ($payment->repayment_id) {
                $repayment = Repayment::find($payment->repayment_id);

                if ($repayment) {
                    $newAmountPaid = $repayment->amount_paid + $amount;
                    $isFullyPaid = $newAmountPaid >= ($repayment->amount_due + $penaltyAmount);

                    $repayment->update([
                        'amount_paid' => $newAmountPaid,
                        'penalty_applied' => $repayment->penalty_applied + $penaltyAmount,
                        'paid_at' => $isFullyPaid ? $payment->paid_at : $repayment->paid_at,
                    ]);
                }
            } else {
                // Apply to oldest unpaid repayment
                $unpaidRepayment = $loan->repayments()->orderBy('due_date')
                    ->whereColumn('amount_paid', '<', 'amount_due')
                    ->first();

                if ($unpaidRepayment) {
                    $newAmountPaid = $unpaidRepayment->amount_paid + $amount;
                    $isFullyPaid = $newAmountPaid >= ($unpaidRepayment->amount_due + $penaltyAmount);

                    $unpaidRepayment->update([
                        'amount_paid' => $newAmountPaid,
                        'penalty_applied' => $unpaidRepayment->penalty_applied + $penaltyAmount,
                        'paid_at' => $isFullyPaid ? $payment->paid_at : $unpaidRepayment->paid_at,
                    ]);
                }
            }

            // Update loan totals
            $newTotalPaid = $loan->total_paid + $amount;
            $newTotalPenalties = $loan->total_penalties + $penaltyAmount;

            // Check if loan is fully paid
            $repayments = $loan->repayments()->get();
            $totalOutstanding = $repayments->sum(function ($rep) {
                $outstanding = $rep->amount_due + $rep->penalty_applied - $rep->amount_paid;
                return max(0, $outstanding);
            });

            $isLoanFullyPaid = $totalOutstanding <= 0.01;

            $loan->update([
                'total_paid' => $newTotalPaid,
                'total_penalties' => $newTotalPenalties,
                'status' => $isLoanFullyPaid ? Loan::ST_CLOSED : $loan->status,
                'is_active' => $isLoanFullyPaid ? false : $loan->is_active,
            ]);

            // Update payment status
            $payment->update([
                'status' => Payment::STATUS_APPROVED,
                'approved_by_user_id' => Auth::id(),
                'approved_at' => now(),
                'remarks' => $request->input('remarks'),
            ]);

            // Create notification for borrower
            if ($loan->borrower_id) {
                $message = "Your payment of ₱" . number_format($amount, 2) . " for loan {$loan->reference} has been approved and recorded.";
                if ($penaltyAmount > 0) {
                    $message .= " Penalty: ₱" . number_format($penaltyAmount, 2);
                }
                if ($isLoanFullyPaid) {
                    $message .= " Loan is now fully paid and closed.";
                }

                \App\Models\Notification::createForBorrower(
                    $loan->borrower_id,
                    \App\Models\Notification::TYPE_PAYMENT_RECEIVED,
                    'Payment Approved',
                    $message,
                    $loan->id
                );
            }

            DB::commit();

            $successMessage = $isLoanFullyPaid
                ? "Payment of ₱" . number_format($amount, 2) . " approved. Loan is now fully paid and closed."
                : "Payment of ₱" . number_format($amount, 2) . " approved successfully.";

            return back()->with('success', $successMessage);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Failed to approve payment: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending payment
     */
    public function reject(Request $request, Payment $payment)
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return back()->withErrors('This payment is not pending approval.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $payment->update([
            'status' => Payment::STATUS_REJECTED,
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by_user_id' => Auth::id(),
        ]);

        // Create notification for borrower
        if ($payment->borrower_id) {
            \App\Models\Notification::createForBorrower(
                $payment->borrower_id,
                \App\Models\Notification::TYPE_INFO,
                'Payment Rejected',
                "Your payment of ₱" . number_format((float) $payment->amount, 2) . " for loan {$payment->loan->reference} has been rejected. Reason: {$validated['rejection_reason']}",
                $payment->loan_id
            );
        }

        return back()->with('success', 'Payment rejected successfully.');
    }
    /**
     * Record a payment for a disbursed loan
     */
    public function store(Request $request, Loan $loan)
    {
        // Validate loan is disbursed
        if ($loan->status !== Loan::ST_DISBURSED) {
            return back()->withErrors("Cannot record payment. Loan status is '{$loan->status}'. Only disbursed loans can receive payments.");
        }

        // Validate loan is active
        if (!$loan->is_active) {
            return back()->withErrors('Cannot record payment. This loan is closed.');
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'repayment_id' => ['nullable', 'exists:repayments,id'],
            'payment_date' => ['nullable', 'date'],
        ]);

        DB::beginTransaction();
        try {
            $amount = (float) $data['amount'];
            $paymentDate = $data['payment_date'] ? CarbonImmutable::parse($data['payment_date']) : CarbonImmutable::now('Asia/Manila');
            $today = CarbonImmutable::now('Asia/Manila')->startOfDay();
            $penaltyAmount = 0;

            // Get repayments
            $repayments = $loan->repayments()->orderBy('due_date')->get();

            // If repayment_id provided, update specific repayment
            if (!empty($data['repayment_id'])) {
                $repayment = $repayments->firstWhere('id', $data['repayment_id']);

                if ($repayment) {
                    $dueDate = CarbonImmutable::parse($repayment->due_date)->startOfDay();
                    // Calculate days overdue (matching TypeScript implementation)
                    // Formula: max(0, floor((today - dueDate) / 86400000) - graceDays)
                    $daysDiff = $dueDate->diffInDays($today, false); // Positive if dueDate is in past
                    $daysOverdue = max(0, (int)floor($daysDiff) - ($loan->penalty_grace_days ?? 0));

                    // Calculate penalty if overdue
                    if ($daysOverdue > 0 && $repayment->amount_paid < $repayment->amount_due) {
                        $outstanding = $repayment->amount_due - $repayment->amount_paid;
                        $penaltyAmount = $outstanding * ($loan->penalty_daily_rate ?? 0.001) * $daysOverdue;
                        $penaltyAmount = round($penaltyAmount * 100) / 100; // Round to 2 decimal places (matching TypeScript)
                    }

                    $newAmountPaid = $repayment->amount_paid + $amount;
                    $isFullyPaid = $newAmountPaid >= ($repayment->amount_due + $penaltyAmount);

                    $repayment->update([
                        'amount_paid' => $newAmountPaid,
                        'penalty_applied' => $repayment->penalty_applied + $penaltyAmount,
                        'paid_at' => $isFullyPaid ? $paymentDate : $repayment->paid_at,
                    ]);
                }
            } else {
                // Apply to oldest unpaid repayment
                $unpaidRepayment = $repayments->first(function ($rep) {
                    return $rep->amount_paid < $rep->amount_due;
                });

                if ($unpaidRepayment) {
                    $dueDate = CarbonImmutable::parse($unpaidRepayment->due_date)->startOfDay();
                    // Calculate days overdue (matching TypeScript implementation)
                    // Formula: max(0, floor((today - dueDate) / 86400000) - graceDays)
                    $daysDiff = $dueDate->diffInDays($today, false); // Positive if dueDate is in past
                    $daysOverdue = max(0, (int)floor($daysDiff) - ($loan->penalty_grace_days ?? 0));

                    if ($daysOverdue > 0) {
                        $outstanding = $unpaidRepayment->amount_due - $unpaidRepayment->amount_paid;
                        $penaltyAmount = $outstanding * ($loan->penalty_daily_rate ?? 0.001) * $daysOverdue;
                        $penaltyAmount = round($penaltyAmount * 100) / 100; // Round to 2 decimal places (matching TypeScript)
                    }

                    $newAmountPaid = $unpaidRepayment->amount_paid + $amount;
                    $isFullyPaid = $newAmountPaid >= ($unpaidRepayment->amount_due + $penaltyAmount);

                    $unpaidRepayment->update([
                        'amount_paid' => $newAmountPaid,
                        'penalty_applied' => $unpaidRepayment->penalty_applied + $penaltyAmount,
                        'paid_at' => $isFullyPaid ? $paymentDate : $unpaidRepayment->paid_at,
                    ]);
                }
            }

            // Update loan totals
            $newTotalPaid = $loan->total_paid + $amount;
            $newTotalPenalties = $loan->total_penalties + $penaltyAmount;

            // Check if loan is fully paid
            $totalOutstanding = $repayments->sum(function ($rep) {
                $outstanding = $rep->amount_due + $rep->penalty_applied - $rep->amount_paid;
                return max(0, $outstanding);
            });

            $isLoanFullyPaid = $totalOutstanding <= 0.01; // Allow small rounding differences

            $loan->update([
                'total_paid' => $newTotalPaid,
                'total_penalties' => $newTotalPenalties,
                'status' => $isLoanFullyPaid ? Loan::ST_CLOSED : $loan->status,
                'is_active' => $isLoanFullyPaid ? false : $loan->is_active,
            ]);

            // Create notification for borrower
            if ($loan->borrower_id) {
                $message = "Payment of ₱" . number_format($amount, 2) . " for loan {$loan->reference} has been recorded.";
                if ($penaltyAmount > 0) {
                    $message .= " Penalty: ₱" . number_format($penaltyAmount, 2);
                }
                if ($isLoanFullyPaid) {
                    $message .= " Loan is now fully paid and closed.";
                }

                \App\Models\Notification::createForBorrower(
                    $loan->borrower_id,
                    \App\Models\Notification::TYPE_PAYMENT_RECEIVED,
                    'Payment Received',
                    $message,
                    $loan->id
                );
            }

            DB::commit();

            $successMessage = $isLoanFullyPaid
                ? "Payment of ₱" . number_format($amount, 2) . " recorded. Loan is now fully paid and closed."
                : "Payment of ₱" . number_format($amount, 2) . " recorded successfully.";

            return back()->with('success', $successMessage);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Failed to record payment: ' . $e->getMessage());
        }
    }
}

