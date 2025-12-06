<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BankAccount;
use App\Models\Loan;
use App\Models\Borrower;
use App\Models\ChartOfAccount;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // timeframe (last 6 months by default)
        $end = Carbon::today();
        $start = (clone $end)->subMonths(5)->startOfMonth(); // 6 months window

        // 1) Cash summary (masterfunds book balance) using your view v_bank_balances if present
        try {
            $totalCash = (float) DB::table('v_bank_balances')->sum('masterfunds_balance');
            $bankBalances = DB::table('v_bank_balances')
                ->select('bank_account_id', 'code', 'name', 'as_of_date', 'bank_balance', 'masterfunds_balance')
                ->get();
        } catch (\Exception $e) {
            // fallback: compute from posted transactions
            $totalCash = (float) DB::table('bank_transactions')
                ->where('status', 'posted')
                ->selectRaw('COALESCE(SUM(COALESCE(received,0)),0) - COALESCE(SUM(COALESCE(spent,0)),0) as bal')
                ->value('bal');
            $bankBalances = DB::table('bank_transactions')
                ->where('status', 'posted')
                ->selectRaw('bank_account_id, COALESCE(SUM(received),0) - COALESCE(SUM(spent),0) as masterfunds_balance')
                ->groupBy('bank_account_id')
                ->get();
        }

        // 2) Revenue by month (for P&L revenue accounts) - aggregated monthly amounts
        $revenueByMonthQuery = DB::table('bank_transactions as bt')
            ->join('chart_of_accounts as coa', 'bt.account_id', '=', 'coa.id')
            ->where('bt.status', 'posted')
            ->where('coa.report', 'Profit and Losses')
            ->where('coa.group_account', 'Revenue (Income)')
            ->whereBetween('bt.tx_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("DATE_FORMAT(bt.tx_date, '%Y-%m') as ym, SUM(COALESCE(bt.received,0) - COALESCE(bt.spent,0)) as amt")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        // Normalize months (ensure months with zero show up)
        $months = [];
        $pointer = (clone $start);
        while ($pointer->lte($end)) {
            $months[] = $pointer->format('Y-m');
            $pointer->addMonth();
        }
        $revenueMap = $revenueByMonthQuery->pluck('amt', 'ym')->toArray();
        $revenueSeries = array_map(function ($m) use ($revenueMap) {
            return round((float) ($revenueMap[$m] ?? 0), 2);
        }, $months);

        // 3) Cashflow by bank (bar chart)
        $bankLabels = $bankBalances->map(fn($r) => ($r->code ?? $r->bank_account_id) . ' — ' . ($r->name ?? '') )->toArray();
        $bankValues = $bankBalances->map(fn($r) => (float) ($r->masterfunds_balance ?? $r->masterfunds_balance) )->toArray();

        // 4) Loans summary
        $totalLoans = (int) DB::table('loans')->count();
        $disbursedLoans = (int) DB::table('loans')->where('status','disbursed')->count();
        $openLoans = (int) DB::table('loans')->whereIn('status',['disbursed','approved','for_release','under_review'])->count();
        $totalPrincipalOutstanding = (float) DB::table('loans')->selectRaw('COALESCE(SUM(total_disbursed - total_paid),0) as outstand')->value('outstand');

        // 5) Loan portfolio by status (pie)
        $loanStatus = DB::table('loans')
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->get()
            ->pluck('cnt','status')
            ->toArray();

        // 6) Top borrowers (by total_disbursed)
        $topBorrowers = DB::table('loans as l')
            ->join('borrowers as b','l.borrower_id','=','b.id')
            ->select('b.id','b.full_name', DB::raw('COALESCE(SUM(l.total_disbursed),0) as total_disbursed'))
            ->groupBy('b.id','b.full_name')
            ->orderByDesc('total_disbursed')
            ->limit(10)
            ->get();

        // 7) Upcoming repayments (next 30 days)
        $today = Carbon::today()->toDateString();
        $upcomingRepayments = DB::table('repayments as r')
            ->join('loans as l','r.loan_id','l.id')
            ->whereRaw('r.amount_paid < r.amount_due')
            ->whereBetween('r.due_date', [ $today, Carbon::today()->addDays(30)->toDateString() ])
            ->select('r.id','r.loan_id','l.reference','l.borrower_name','r.due_date','r.amount_due','r.amount_paid')
            ->orderBy('r.due_date')
            ->limit(20)
            ->get();

        // 8) Recent bank transactions (posted)
        $recentTx = DB::table('bank_transactions')
            ->where('status','posted')
            ->orderByDesc('tx_date')
            ->limit(10)
            ->get();

        // Pass data to view (encode series for Chart.js)
        return view('dashboard.index', [
            'totalCash' => $totalCash,
            'bankBalances' => $bankBalances,
            'months' => $months,
            'revenueSeries' => $revenueSeries,
            'bankLabels' => $bankLabels,
            'bankValues' => $bankValues,
            'totalLoans' => $totalLoans,
            'disbursedLoans' => $disbursedLoans,
            'openLoans' => $openLoans,
            'totalPrincipalOutstanding' => $totalPrincipalOutstanding,
            'loanStatus' => $loanStatus,
            'topBorrowers' => $topBorrowers,
            'upcomingRepayments' => $upcomingRepayments,
            'recentTx' => $recentTx,
        ]);
    }
}