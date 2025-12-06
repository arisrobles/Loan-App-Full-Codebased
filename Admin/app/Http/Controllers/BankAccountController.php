<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    /**
     * List bank accounts with basic stats.
     */
    public function index(Request $request)
    {
        $q       = $request->query('q');
        $perPage = max(5, min((int) $request->query('per_page', 10), 100));

        $query = BankAccount::query()
            ->withCount('transactions')
            ->withCount('loans')
            ->orderBy('id');

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            });
        }

        $accounts = $query->paginate($perPage)->withQueryString();

        $meta = [
            'total'     => $accounts->total(),
            'page'      => $accounts->currentPage(),
            'per_page'  => $accounts->perPage(),
            'last_page' => $accounts->lastPage(),
            'query'     => $request->query(),
        ];

        // If you want to use the v_bank_balances view:
        // $balances = DB::table('v_bank_balances')
        //     ->whereIn('bank_account_id', $accounts->pluck('id'))
        //     ->get()
        //     ->keyBy('bank_account_id');

        return view('bank-accounts.index', compact('accounts', 'meta'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('bank-accounts.create');
    }

    /**
     * Store new bank account.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'     => ['required', 'string', 'max:32', 'unique:bank_accounts,code'],
            'name'     => ['required', 'string', 'max:128'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        if (empty($data['timezone'])) {
            $data['timezone'] = 'Asia/Manila';
        }

        BankAccount::create($data);

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Bank account created successfully.');
    }

    /**
     * Show a single bank account with recent transactions.
     */
    public function show(BankAccount $bankAccount)
    {
        // Latest 10 transactions for that account
        $transactions = BankTransaction::where('bank_account_id', $bankAccount->id)
            ->orderByDesc('tx_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // Optional: pull balances from the v_bank_balances view (if present)
        $balance = null;
        try {
            $balance = DB::table('v_bank_balances')
                ->where('bank_account_id', $bankAccount->id)
                ->first();
        } catch (\Throwable $e) {
            // view might not exist — ignore
        }

        return view('bank-accounts.show', [
            'account'      => $bankAccount,
            'transactions' => $transactions,
            'balance'      => $balance,
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(BankAccount $bankAccount)
    {
        return view('bank-accounts.edit', ['account' => $bankAccount]);
    }

    /**
     * Update a bank account.
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $request->validate([
            'code'     => ['required', 'string', 'max:32', 'unique:bank_accounts,code,' . $bankAccount->id],
            'name'     => ['required', 'string', 'max:128'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        if (empty($data['timezone'])) {
            $data['timezone'] = 'Asia/Manila';
        }

        $bankAccount->update($data);

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Bank account updated successfully.');
    }

    /**
     * Delete a bank account (blocks if it has transactions).
     */
    public function destroy(BankAccount $bankAccount)
    {
        // Safety check — don’t delete accounts that still have transactions.
        $txCount = $bankAccount->transactions()->count();
        if ($txCount > 0) {
            return back()->withErrors("Cannot delete this bank account because it has {$txCount} transaction(s).");
        }

        $loanCount = $bankAccount->loans()->count();
        if ($loanCount > 0) {
            return back()->withErrors("Cannot delete this bank account because it is used on {$loanCount} loan(s).");
        }

        $bankAccount->delete();

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Bank account deleted.');
    }
}