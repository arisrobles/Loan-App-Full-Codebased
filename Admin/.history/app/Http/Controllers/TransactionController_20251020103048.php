<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display transactions for a specific bank account
     */
    public function index(Request $request, $accountId)
    {
        $q = $request->input('q');

        // ✅ Base query
        $transactions = BankTransaction::where('bank_account_id', $accountId)
            ->when($q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('contact_display', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('account_name', 'like', "%{$q}%")
                        ->orWhere('tx_class', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('tx_date')
            ->paginate(15)
            ->appends(['q' => $q]);

        // ✅ Summary section
        $bankEnding = DB::table('bank_transactions')
            ->where('bank_account_id', $accountId)
            ->where('status', 'posted')
            ->selectRaw('COALESCE(SUM(received),0) - COALESCE(SUM(spent),0) AS balance')
            ->value('balance') ?? 0;

        $postedBal = DB::table('bank_transactions')
            ->where('bank_account_id', $accountId)
            ->where('status', 'posted')
            ->sum(DB::raw('COALESCE(received,0) - COALESCE(spent,0)'));

        $counts = [
            'pending'  => BankTransaction::where('bank_account_id', $accountId)->where('status', 'pending')->count(),
            'excluded' => BankTransaction::where('bank_account_id', $accountId)->where('status', 'excluded')->count(),
        ];

        return view('transactions.index', [
            'transactions' => $transactions,
            'accountId'    => $accountId,
            'bankEnding'   => $bankEnding,
            'postedBal'    => $postedBal,
            'counts'       => $counts,
            'q'            => $q,
        ]);
    }

    /**
     * Mark a transaction as posted
     */
    public function post($accountId, $transactionId)
    {
        $tx = BankTransaction::where('bank_account_id', $accountId)->findOrFail($transactionId);
        $tx->update(['status' => 'posted', 'posted_at' => now()]);
        return back()->with('status', 'Transaction marked as posted.');
    }

    /**
     * Exclude a transaction
     */
    public function exclude($accountId, $transactionId)
    {
        $tx = BankTransaction::where('bank_account_id', $accountId)->findOrFail($transactionId);
        $tx->update(['status' => 'excluded']);
        return back()->with('status', 'Transaction excluded.');
    }

    /**
     * Restore an excluded transaction
     */
    public function restore($accountId, $transactionId)
    {
        $tx = BankTransaction::where('bank_account_id', $accountId)->findOrFail($transactionId);
        $tx->update(['status' => 'pending']);
        return back()->with('status', 'Transaction restored to pending.');
    }
}
