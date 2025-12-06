<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BankAccount;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Financial reports: Profit & Loss + Balance Sheet + Cash Flow
     *
     * Uses:
     *  - bank_transactions (only status = posted)
     *  - chart_of_accounts joined on bt.account_id = coa.id
     *
     * Notes:
     *  - ChartOfAccount columns used: id, code, name, group_account, report, normal_balance
     *  - group_account values are expected from ChartOfAccount::GROUPS (Assets, Liabilities, Equity,
     *    Revenue (Income), Expense (COGS), Expenses)
     */
    public function index(Request $request)
    {
        // Raw inputs (keep original string form to re-populate the form)
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        // Normalize inputs to dates for queries (nullable)
        $from = $fromInput ? Carbon::parse($fromInput)->startOfDay()->toDateString() : null;
        $to   = $toInput   ? Carbon::parse($toInput)->endOfDay()->toDateString()   : null;

        // Optional bank account filter
        $bankAccountId = $request->integer('bank_account_id');

        // Load bank accounts for form dropdown
        $bankAccounts = BankAccount::orderBy('name')->get();

        //
        // ---------------------------
        // 1) PROFIT & LOSS (P&L)
        // ---------------------------
        $plQuery = DB::table('bank_transactions as bt')
            ->join('chart_of_accounts as coa', 'bt.account_id', '=', 'coa.id')
            ->selectRaw('
                coa.id,
                coa.code,
                coa.name,
                coa.group_account,
                SUM(COALESCE(bt.received, 0)) as total_received,
                SUM(COALESCE(bt.spent, 0))    as total_spent
            ')
            ->whereRaw("LOWER(bt.status) = 'posted'")
            ->where('coa.report', 'Profit and Losses')
            ->groupBy('coa.id', 'coa.code', 'coa.name', 'coa.group_account')
            ->orderBy('coa.group_account')
            ->orderBy('coa.code');

        // apply date filter if provided
        if ($from && $to) {
            $plQuery->whereBetween('bt.tx_date', [$from, $to]);
        } elseif ($from) {
            $plQuery->whereDate('bt.tx_date', '>=', $from);
        } elseif ($to) {
            $plQuery->whereDate('bt.tx_date', '<=', $to);
        }

        if ($bankAccountId) {
            $plQuery->where('bt.bank_account_id', $bankAccountId);
        }

        $plRaw = $plQuery->get();

        $plRows = $plRaw->map(function ($row) {
            // Revenue accounts: received - spent
            if ($row->group_account === 'Revenue (Income)') {
                $row->amount = ($row->total_received ?? 0) - ($row->total_spent ?? 0);
            } else {
                // Expense & COGS: show as positive cost: spent - received
                $row->amount = ($row->total_spent ?? 0) - ($row->total_received ?? 0);
            }
            return $row;
        });

        $revenues = $plRows->where('group_account', 'Revenue (Income)');
        $cogs     = $plRows->where('group_account', 'Expense (COGS)');
        $expenses = $plRows->where('group_account', 'Expenses');

        $totalRevenue = $revenues->sum('amount');
        $totalCogs    = $cogs->sum('amount');
        $totalExpense = $expenses->sum('amount');

        $grossProfit  = $totalRevenue - $totalCogs;
        $netProfit    = $grossProfit - $totalExpense;

        //
        // ---------------------------
        // 2) BALANCE SHEET
        // ---------------------------
        // We treat Assets differently (received - spent) than Liabilities/Equity (spent - received)
        $bsQuery = DB::table('bank_transactions as bt')
            ->join('chart_of_accounts as coa', 'bt.account_id', '=', 'coa.id')
            ->selectRaw('
                coa.id,
                coa.code,
                coa.name,
                coa.group_account,
                coa.normal_balance,
                SUM(COALESCE(bt.received,0)) as total_received,
                SUM(COALESCE(bt.spent,0))    as total_spent
            ')
            ->whereRaw("LOWER(bt.status) = 'posted'")
            ->where('coa.report', 'Balance Sheets')
            ->groupBy('coa.id', 'coa.code', 'coa.name', 'coa.group_account', 'coa.normal_balance')
            ->orderBy('coa.group_account')
            ->orderBy('coa.code');

        // date filter: for balance sheet, commonly up to 'to' date (inclusive); if none provided, use <= today
        if ($from && $to) {
            $bsQuery->whereBetween('bt.tx_date', [$from, $to]);
        } elseif ($from) {
            $bsQuery->whereDate('bt.tx_date', '>=', $from);
        } elseif ($to) {
            $bsQuery->whereDate('bt.tx_date', '<=', $to);
        } else {
            // default: include all up to today
            $bsQuery->whereDate('bt.tx_date', '<=', Carbon::today()->toDateString());
        }

        if ($bankAccountId) {
            $bsQuery->where('bt.bank_account_id', $bankAccountId);
        }

        $bsRaw = $bsQuery->get();

        // compute amounts per chart account
        $bsRows = $bsRaw->map(function ($row) {
            if ($row->group_account === 'Assets') {
                // Assets: positive when received > spent
                $row->amount = ($row->total_received ?? 0) - ($row->total_spent ?? 0);
            } else {
                // Liabilities & Equity: show as positive liabilities when spent > received
                $row->amount = ($row->total_spent ?? 0) - ($row->total_received ?? 0);
            }
            return $row;
        });

        // group into BS sections
        $bsAssets = $bsRows->where('group_account', 'Assets');
        $bsLiabs  = $bsRows->where('group_account', 'Liabilities');
        $bsEquity = $bsRows->where('group_account', 'Equity');

        $totalAssets = $bsAssets->sum('amount');
        $totalLiabs  = $bsLiabs->sum('amount');
        $totalEquity = $bsEquity->sum('amount');

        // simple balancing check (assets vs liabilities + equity)
        $bsBalanced = round($totalAssets, 2) === round($totalLiabs + $totalEquity, 2);

        //
        // ---------------------------
        // 3) CASH FLOW (simple)
        // ---------------------------
        // We'll compute total cash in/out for posted transactions in the period, and a breakdown by COA group.
        $cfQuery = DB::table('bank_transactions as bt')
            ->join('chart_of_accounts as coa', 'bt.account_id', '=', 'coa.id')
            ->selectRaw('
                coa.group_account,
                SUM(COALESCE(bt.received,0)) as total_received,
                SUM(COALESCE(bt.spent,0))    as total_spent
            ')
            ->whereRaw("LOWER(bt.status) = 'posted'")
            ->groupBy('coa.group_account')
            ->orderBy('coa.group_account');

        if ($from && $to) {
            $cfQuery->whereBetween('bt.tx_date', [$from, $to]);
        } elseif ($from) {
            $cfQuery->whereDate('bt.tx_date', '>=', $from);
        } elseif ($to) {
            $cfQuery->whereDate('bt.tx_date', '<=', $to);
        }

        if ($bankAccountId) {
            $cfQuery->where('bt.bank_account_id', $bankAccountId);
        }

        $cfRaw = $cfQuery->get();

        $cashIn  = 0.0;
        $cashOut = 0.0;
        $cfByGroup = [];

        foreach ($cfRaw as $g) {
            $r = (float) ($g->total_received ?? 0);
            $s = (float) ($g->total_spent ?? 0);
            $cashIn += $r;
            $cashOut += $s;
            $cfByGroup[$g->group_account] = [
                'in'  => $r,
                'out' => $s,
                'net' => $r - $s,
            ];
        }

        $cfNet = $cashIn - $cashOut;

        //
        // ---------------------------
        // Return view with everything
        // ---------------------------
        return view('reports.index', [
            // original inputs for form
            'from'           => $fromInput,
            'to'             => $toInput,
            'bankAccountId'  => $bankAccountId,
            'bankAccounts'   => $bankAccounts,

            // Profit & Loss
            'revenues'       => $revenues,
            'cogs'           => $cogs,
            'expenses'       => $expenses,
            'totalRevenue'   => $totalRevenue,
            'totalCogs'      => $totalCogs,
            'totalExpense'   => $totalExpense,
            'grossProfit'    => $grossProfit,
            'netProfit'      => $netProfit,

            // Balance Sheet
            'bsAssets'       => $bsAssets,
            'bsLiabs'        => $bsLiabs,
            'bsEquity'       => $bsEquity,
            'totalAssets'    => $totalAssets,
            'totalLiabs'     => $totalLiabs,
            'totalEquity'    => $totalEquity,
            'bsBalanced'     => $bsBalanced,
            'bsRows'         => $bsRows,

            // Cash Flow
            'cashIn'         => $cashIn,
            'cashOut'        => $cashOut,
            'cfNet'          => $cfNet,
            'cfByGroup'      => $cfByGroup,
        ]);
    }
}