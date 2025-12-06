<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Repayment;
use App\Models\Borrower;
use App\Models\Payment;
use App\Helpers\LoanHelper;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $status  = $request->query('status');
        $q       = $request->query('q');
        $months  = max(1, min((int) $request->query('months', 6), 24));
        $perPage = max(5, min((int) $request->query('per_page', 10), 100));

        $loans = Loan::query()
            ->status($status)
            ->search($q)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $tileRows = Loan::select('status', DB::raw('COUNT(*) as cnt'))
            ->whereIn('status', [
                Loan::ST_NEW, Loan::ST_REVIEW, Loan::ST_APPROVED,
                Loan::ST_FOR_RELEASE, Loan::ST_DISBURSED,
            ])
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $summary = [
            'new_application' => (int) ($tileRows[Loan::ST_NEW] ?? 0),
            'under_review'    => (int) ($tileRows[Loan::ST_REVIEW] ?? 0),
            'approved'        => (int) ($tileRows[Loan::ST_APPROVED] ?? 0),
            'for_release'     => (int) ($tileRows[Loan::ST_FOR_RELEASE] ?? 0),
            'disbursed'       => (int) ($tileRows[Loan::ST_DISBURSED] ?? 0),
            'missed'          => Repayment::whereColumn('amount_paid','<','amount_due')
                                   ->whereDate('due_date','<', now('Asia/Manila')->toDateString())
                                   ->count(),
        ];

        $missedQuery = Repayment::with('loan:id,reference,borrower_name,penalty_grace_days,penalty_daily_rate')
            ->whereColumn('amount_paid', '<', 'amount_due')
            ->whereDate('due_date', '<', now('Asia/Manila')->toDateString())
            ->orderBy('due_date')
            ->take(5)
            ->get();

        $missed = [];
        foreach ($missedQuery as $r) {
            $missed[] = [
                'repayment_id'      => $r->id,
                'reference'         => optional($r->loan)->reference,
                'borrower_name'     => optional($r->loan)->borrower_name,
                'overdue_amount'    => (string) $r->outstanding,
                'due_date'          => optional($r->due_date)->toDateString(),
                'days_overdue'      => $r->days_overdue,
                'suggested_penalty' => $r->computePenalty(),
            ];
        }

        $end = CarbonImmutable::now('Asia/Manila')->startOfMonth();
        $chartLabels = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $chartLabels[] = $end->subMonths($i)->format('Y-m');
        }

        $statusMap = [
            'New'           => Loan::ST_NEW,
            'Under Review'  => Loan::ST_REVIEW,
            'Approved'      => Loan::ST_APPROVED,
            'For Release'   => Loan::ST_FOR_RELEASE,
            'Disbursed'     => Loan::ST_DISBURSED,
        ];

        $from = $end->subMonths($months - 1)->toDateString();

        $raw = Loan::selectRaw("DATE_FORMAT(application_date, '%Y-%m') as ym, status, COUNT(*) as cnt")
            ->whereDate('application_date', '>=', $from)
            ->whereIn('status', array_values($statusMap))
            ->groupBy('ym', 'status')
            ->get();

        $map = [];
        foreach ($raw as $r) {
            $map[$r->ym][$r->status] = (int) $r->cnt;
        }

        $chartDatasets = [];
        foreach ($statusMap as $legend => $code) {
            $series = [];
            foreach ($chartLabels as $ym) {
                $series[] = (int) ($map[$ym][$code] ?? 0);
            }
            $chartDatasets[$legend] = $series;
        }

        return view('loan', [
            'loans'         => $loans,
            'summary'       => $summary,
            'missed'        => $missed,
            'chartLabels'   => $chartLabels,
            'chartDatasets' => $chartDatasets,
            'filters'       => [
                'status'   => $status,
                'q'        => $q,
                'months'   => $months,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function transition(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'to_status'    => ['required', Rule::in([
                Loan::ST_REVIEW, Loan::ST_APPROVED, Loan::ST_FOR_RELEASE,
                Loan::ST_DISBURSED, Loan::ST_CLOSED, Loan::ST_REJECTED,
                Loan::ST_CANCELLED, Loan::ST_RESTRUCTURED,
            ])],
            'release_date' => ['nullable','date'],
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $loan->status;
            $to = $data['to_status'];

            if ($to === Loan::ST_DISBURSED) {
                $loan->update([
                    'status'          => $to,
                    'release_date'    => $data['release_date'] ?? now('Asia/Manila')->toDateString(),
                    'total_disbursed' => $loan->principal_amount,
                    'is_active'       => 1,
                ]);
            } elseif (in_array($to, [Loan::ST_CLOSED, Loan::ST_REJECTED, Loan::ST_CANCELLED], true)) {
                $loan->update(['status' => $to, 'is_active' => 0]);
            } else {
                $loan->update(['status' => $to]);
            }

            // Refresh loan to get updated data
            $loan->refresh();

            // Create notification for borrower if status changed
            if ($oldStatus !== $to && $loan->borrower_id) {
                NotificationHelper::notifyLoanStatusChange($loan, $oldStatus, $to);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Failed to update status: '.$e->getMessage());
        }

        return back()->with('success', "Loan {$loan->reference} moved to {$data['to_status']}.");
    }

    /**
     * Show the form for creating a new loan
     */
    public function create()
    {
        $borrowers = Borrower::where('is_archived', false)
            ->where('status', '!=', 'blacklisted')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email', 'phone']);

        return view('loans.create', compact('borrowers'));
    }

    /**
     * Store a newly created loan
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'borrower_id' => ['required', 'exists:borrowers,id'],
            'principal_amount' => ['required', 'numeric', 'min:3500', 'max:50000'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tenor' => ['required', 'in:6,12,36'],
            'application_date' => ['required', 'date'],
            'penalty_grace_days' => ['nullable', 'integer', 'min:0'],
            'penalty_daily_rate' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            // Get borrower
            $borrower = Borrower::findOrFail($data['borrower_id']);

            // Set defaults
            $interestRate = ($data['interest_rate'] ?? 24) / 100; // Convert percentage to decimal (24% = 0.24)
            $applicationDate = Carbon::parse($data['application_date'])->startOfDay();
            $tenor = (int) $data['tenor'];

            // Calculate maturity date
            $maturityDate = LoanHelper::calculateMaturityDate($applicationDate, $tenor);

            // Calculate EMI
            $monthlyEMI = LoanHelper::calculateEMI($data['principal_amount'], $data['interest_rate'] ?? 24, $tenor);

            // Generate loan reference
            $reference = LoanHelper::generateLoanReference();

            // Create loan
            $loan = Loan::create([
                'reference' => $reference,
                'borrower_id' => $borrower->id,
                'borrower_name' => $borrower->full_name,
                'principal_amount' => $data['principal_amount'],
                'interest_rate' => $interestRate,
                'application_date' => $applicationDate,
                'maturity_date' => $maturityDate,
                'status' => Loan::ST_NEW,
                'total_disbursed' => 0,
                'total_paid' => 0,
                'total_penalties' => 0,
                'penalty_grace_days' => $data['penalty_grace_days'] ?? 0,
                'penalty_daily_rate' => $data['penalty_daily_rate'] ?? 0.001000,
                'is_active' => true,
                'remarks' => $data['remarks'] ?? 'Admin panel application',
            ]);

            // Generate payment schedule
            $scheduleData = LoanHelper::generatePaymentSchedule($applicationDate, $tenor, $monthlyEMI);

            // Create repayment records
            foreach ($scheduleData as $index => $item) {
                Repayment::create([
                    'loan_id' => $loan->id,
                    'due_date' => $item['dueDate'] instanceof \Carbon\Carbon
                        ? $item['dueDate']->toDateString()
                        : $item['dueDate'],
                    'amount_due' => $item['amount'],
                    'amount_paid' => 0,
                    'penalty_applied' => 0,
                    'note' => $index === 0 ? 'First payment' : "Payment " . ($index + 1),
                ]);
            }

            // Create notification for borrower
            if ($loan->borrower_id) {
                NotificationHelper::notifyLoanCreated($loan);
            }

            DB::commit();

            return redirect()
                ->route('loans.index')
                ->with('success', "Loan {$reference} created successfully with {$tenor} repayment schedules.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors('Failed to create loan: ' . $e->getMessage());
        }
    }

    /**
     * Show detailed loan information
     */
    public function show(Loan $loan)
    {
        // Load relationships
        $loan->load([
            'borrower',
            'repayments' => function($query) {
                $query->orderBy('due_date');
            },
            'payments' => function($query) {
                $query->with(['receiptDocument', 'approvedBy'])
                      ->orderBy('paid_at', 'desc')
                      ->orderBy('created_at', 'desc');
            }
        ]);

        // Group payments by repayment_id for easier display
        $paymentsByRepayment = $loan->payments->groupBy('repayment_id');

        // Calculate totals
        $totalDue = $loan->repayments->sum('amount_due');
        $totalPaid = $loan->repayments->sum('amount_paid');
        $totalPenalties = $loan->repayments->sum('penalty_applied');
        $totalOutstanding = $loan->repayments->sum(function($rep) {
            return max(0, (float)$rep->amount_due + (float)$rep->penalty_applied - (float)$rep->amount_paid);
        });

        // Payment statistics
        $paymentStats = [
            'approved' => $loan->payments->where('status', Payment::STATUS_APPROVED)->sum('amount'),
            'pending' => $loan->payments->where('status', Payment::STATUS_PENDING)->sum('amount'),
            'rejected' => $loan->payments->where('status', Payment::STATUS_REJECTED)->sum('amount'),
            'total_approved' => $loan->payments->where('status', Payment::STATUS_APPROVED)->count(),
            'total_pending' => $loan->payments->where('status', Payment::STATUS_PENDING)->count(),
            'total_rejected' => $loan->payments->where('status', Payment::STATUS_REJECTED)->count(),
        ];

        return view('loans.show', compact('loan', 'paymentsByRepayment', 'totalDue', 'totalPaid', 'totalPenalties', 'totalOutstanding', 'paymentStats'));
    }

    /**
     * Show the form for editing a loan
     */
    public function edit(Loan $loan)
    {
        $borrowers = Borrower::where('is_archived', false)
            ->where('status', '!=', 'blacklisted')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email', 'phone']);

        return view('loans.edit', compact('loan', 'borrowers'));
    }

    /**
     * Update a loan
     */
    public function update(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'borrower_id' => ['required', 'exists:borrowers,id'],
            'principal_amount' => ['required', 'numeric', 'min:3500', 'max:50000'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'application_date' => ['required', 'date'],
            'release_date' => ['nullable', 'date'],
            'penalty_grace_days' => ['nullable', 'integer', 'min:0'],
            'penalty_daily_rate' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            // Get borrower
            $borrower = Borrower::findOrFail($data['borrower_id']);

            // Set defaults
            $interestRate = ($data['interest_rate'] ?? $loan->interest_rate * 100) / 100;
            $applicationDate = Carbon::parse($data['application_date'])->startOfDay();

            // Update loan
            $loan->update([
                'borrower_id' => $borrower->id,
                'borrower_name' => $borrower->full_name,
                'principal_amount' => $data['principal_amount'],
                'interest_rate' => $interestRate,
                'application_date' => $applicationDate,
                'release_date' => $data['release_date'] ? Carbon::parse($data['release_date'])->startOfDay() : $loan->release_date,
                'penalty_grace_days' => $data['penalty_grace_days'] ?? $loan->penalty_grace_days,
                'penalty_daily_rate' => $data['penalty_daily_rate'] ?? $loan->penalty_daily_rate,
                'remarks' => $data['remarks'] ?? $loan->remarks,
            ]);

            DB::commit();

            return redirect()
                ->route('loans.show', $loan)
                ->with('success', "Loan {$loan->reference} updated successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors('Failed to update loan: ' . $e->getMessage());
        }
    }
}
