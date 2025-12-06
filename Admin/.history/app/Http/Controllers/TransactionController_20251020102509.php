<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     */
    public function index(Request $request, $accountId)
    {
        // Filters
        $search = $request->input('q');
        $status = $request->input('status');

        // Base query
        $query = Transaction::where('account_id', $accountId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('contact_display', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $transactions = $query->orderByDesc('tx_date')->paginate(15);

        // Summaries
        $bankEnding = Transaction::where('account_id', $accountId)
            ->sum('received') - Transaction::where('account_id', $accountId)
            ->sum('spent');

        $postedBal = Transaction::where('account_id', $accountId)
            ->where('status', 'posted')
            ->sum('received');

        $counts = [
            'pending' => Transaction::where('account_id', $accountId)->where('status', 'pending')->count(),
            'excluded' => Transaction::where('account_id', $accountId)->where('status', 'excluded')->count(),
            'posted' => Transaction::where('account_id', $accountId)->where('status', 'posted')->count(),
        ];

        return view('transactions.index', compact(
            'transactions',
            'bankEnding',
            'postedBal',
            'counts',
            'accountId'
        ));
    }

    /**
     * Mark a transaction as posted.
     */
    public function post($accountId, $transactionId)
    {
        $tx = Transaction::where('account_id', $accountId)->findOrFail($transactionId);
        $tx->status = 'posted';
        $tx->posted_at = Carbon::now();
        $tx->save();

        return redirect()->back()->with('success', 'Transaction posted successfully.');
    }

    /**
     * Exclude a transaction.
     */
    public function exclude($accountId, $transactionId)
    {
        $tx = Transaction::where('account_id', $accountId)->findOrFail($transactionId);
        $tx->status = 'excluded';
        $tx->save();

        return redirect()->back()->with('success', 'Transaction excluded.');
    }

    /**
     * Restore an excluded transaction.
     */
    public function restore($accountId, $transactionId)
    {
        $tx = Transaction::where('account_id', $accountId)->findOrFail($transactionId);
        $tx->status = 'pending';
        $tx->save();

        return redirect()->back()->with('success', 'Transaction restored to pending.');
    }
}
