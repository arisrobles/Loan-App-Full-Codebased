<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;

class BankTransactionController extends Controller
{
    /**
     * List all bank transactions for a given bank account, with filters.
     */public function index(Request $request, int $accountId)
{
    $account = $this->findAccount($accountId);

    $perPage = $request->integer('per_page', 25);

    $query = BankTransaction::with('account')     // <-- eager load COA
        ->where('bank_account_id', $accountId)
        ->orderByDesc('tx_date')
        ->orderByDesc('id');

    if ($status = $request->get('status')) {
        $query->where('status', $status);
    }

    if ($reconcile = $request->get('reconcile_status')) {
        $query->where('reconcile_status', $reconcile);
    }

    if ($kind = $request->get('kind')) {
        $query->where('kind', $kind);
    }

    if ($q = $request->get('q')) {
        $query->where(function ($sub) use ($q) {
            $sub->where('contact_display', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orWhere('ledger_contact', 'like', "%{$q}%")
                ->orWhere('account_name', 'like', "%{$q}%")
                ->orWhere('remarks', 'like', "%{$q}%")
                ->orWhere('tx_class', 'like', "%{$q}%")
                ->orWhere('source', 'like', "%{$q}%")
                ->orWhere('ref_code', 'like', "%{$q}%");
        });
    }

    $rows = $query->paginate($perPage)->withQueryString();

    $meta = [
        'page'      => $rows->currentPage(),
        'last_page' => $rows->lastPage(),
        'per_page'  => $rows->perPage(),
        'total'     => $rows->total(),
        'query'     => $request->query(),
    ];

    // Load all COA options for the "Add Transaction" form
    $accounts = ChartOfAccount::orderBy('code')->get();

    return view('transactions.index', compact('account', 'rows', 'meta', 'accounts'));
}
    /**
     * Manual add: create a single bank transaction for this account.
     */
    public function store(Request $request, int $accountId)
    {
        $account = $this->findAccount($accountId);
    
        $data = $request->validate([
            'ref_code'        => ['nullable', 'string', 'max:32'],
            'kind'            => ['required', 'in:bank,journal'],
            'tx_date'         => ['required', 'date'],
            'contact_display' => ['nullable', 'string', 'max:191'],
            'description'     => ['nullable', 'string', 'max:255'],
            'spent'           => ['nullable', 'numeric', 'min:0'],
            'received'        => ['nullable', 'numeric', 'min:0'],
            'reconcile_status'=> ['nullable', 'in:pending,ok,match'],
            'ledger_contact'  => ['nullable', 'string', 'max:191'],
            'account_name'    => ['nullable', 'string', 'max:191'],
            'remarks'         => ['nullable', 'string', 'max:255'],
            'tx_class'        => ['nullable', 'string', 'max:191'],
            'source'          => ['nullable', 'string', 'max:64'],
            'status'          => ['nullable', 'in:pending,posted,excluded'],
            'is_transfer'     => ['nullable', 'boolean'],
            'bank_text'       => ['nullable', 'string', 'max:255'],
            'match_id'        => ['nullable', 'integer'],
    
            // NEW: link to Chart of Accounts
            'account_id'      => ['required', 'exists:chart_of_accounts,id'],
        ]);
    
        $data['bank_account_id']  = $account->id;
        $data['reconcile_status'] = $data['reconcile_status'] ?? 'pending';
        $data['status']           = $data['status'] ?? 'pending';
        $data['is_transfer']      = $data['is_transfer'] ?? 0;
    
        BankTransaction::create($data);
    
        return redirect()
            ->route('transactions.index', $account->id)
            ->with('success', 'Bank transaction added.');
    }
    /**
     * Simple CSV import.
     * 
     * Expected CSV header (example):
     * tx_date,description,contact_display,spent,received,ref_code
     */
    public function import(Request $request, int $accountId)
    {
        $account = $this->findAccount($accountId);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->with('error', 'Unable to read uploaded file.');
        }

        $header = fgetcsv($handle, 0, ',');
        if (! $header) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty.');
        }

        // Map header names to indexes
        $map = [];
        foreach ($header as $index => $name) {
            $key = strtolower(trim($name));
            $map[$key] = $index;
        }

        $requiredCols = ['tx_date', 'description', 'spent', 'received'];
        foreach ($requiredCols as $col) {
            if (! array_key_exists($col, $map)) {
                fclose($handle);
                return back()->with('error', "CSV missing required column: {$col}");
            }
        }

        $created = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue; // skip empty lines
            }

            $txDate   = $row[$map['tx_date']] ?? null;
            $desc     = $row[$map['description']] ?? null;
            $contact  = $map['contact_display'] ?? null ? ($row[$map['contact_display']] ?? null) : null;
            $spent    = $row[$map['spent']] ?? null;
            $received = $row[$map['received']] ?? null;
            $ref      = $map['ref_code'] ?? null ? ($row[$map['ref_code']] ?? null) : null;

            if (empty($txDate) || (empty($spent) && empty($received))) {
                continue;
            }

            BankTransaction::create([
                'bank_account_id'  => $account->id,
                'ref_code'         => $ref,
                'kind'             => 'bank',
                'tx_date'          => $txDate,
                'contact_display'  => $contact,
                'description'      => $desc,
                'spent'            => $spent !== '' ? (float) $spent : null,
                'received'         => $received !== '' ? (float) $received : null,
                'reconcile_status' => 'pending',
                'status'           => 'pending',
                'is_transfer'      => 0,
                'source'           => 'CSV import',
            ]);

            $created++;
        }

        fclose($handle);

        return redirect()
            ->route('transactions.index', $account->id)
            ->with('success', "Imported {$created} transaction(s) from CSV.");
    }

    /**
     * Update reconcile status and some matching info for a transaction.
     */
    public function reconcile(Request $request, int $accountId, int $transactionId)
    {
        $account     = $this->findAccount($accountId);
        $transaction = $this->findTransaction($accountId, $transactionId);

        $data = $request->validate([
            'reconcile_status' => ['required', 'in:pending,ok,match'],
            'remarks'          => ['nullable', 'string', 'max:255'],
            'match_id'         => ['nullable', 'integer'],
            'ledger_contact'   => ['nullable', 'string', 'max:191'],
            'account_name'     => ['nullable', 'string', 'max:191'],
            'tx_class'         => ['nullable', 'string', 'max:191'],
            'source'           => ['nullable', 'string', 'max:64'],
        ]);

        $transaction->update($data);

        return redirect()
            ->route('transactions.index', $account->id)
            ->with('success', "Transaction #{$transaction->id} reconciled as {$transaction->reconcile_status}.");
    }

    /**
     * Mark transaction as posted (hits your books).
     */
    public function markPosted(int $accountId, int $transactionId)
    {
        $account     = $this->findAccount($accountId);
        $transaction = $this->findTransaction($accountId, $transactionId);

        $transaction->update([
            'status'    => 'posted',
            'posted_at' => now(),
        ]);

        return redirect()
            ->route('transactions.index', $account->id)
            ->with('success', "Transaction #{$transaction->id} marked as posted.");
    }

    // app/Http/Controllers/BankTransactionController.php

public function edit(int $accountId, int $transactionId)
{
    $account     = $this->findAccount($accountId);
    $transaction = $this->findTransaction($accountId, $transactionId);

    return view('transactions.edit', compact('account', 'transaction'));
}

public function update(Request $request, int $accountId, int $transactionId)
{
    $account     = $this->findAccount($accountId);
    $transaction = $this->findTransaction($accountId, $transactionId);

    $data = $request->validate([
        'ref_code'        => ['nullable', 'string', 'max:32'],
        'kind'            => ['required', 'in:bank,journal'],
        'tx_date'         => ['required', 'date'],
        'contact_display' => ['nullable', 'string', 'max:191'],
        'description'     => ['nullable', 'string', 'max:255'],
        'spent'           => ['nullable', 'numeric', 'min:0'],
        'received'        => ['nullable', 'numeric', 'min:0'],
        'reconcile_status'=> ['nullable', 'in:pending,ok,match'],
        'ledger_contact'  => ['nullable', 'string', 'max:191'],
        'account_name'    => ['nullable', 'string', 'max:191'],
        'remarks'         => ['nullable', 'string', 'max:255'],
        'tx_class'        => ['nullable', 'string', 'max:191'],
        'source'          => ['nullable', 'string', 'max:64'],
        'status'          => ['nullable', 'in:pending,posted,excluded'],
        'is_transfer'     => ['nullable', 'boolean'],
        'bank_text'       => ['nullable', 'string', 'max:255'],
        'match_id'        => ['nullable', 'integer'],
    ]);

    $data['reconcile_status'] = $data['reconcile_status'] ?? $transaction->reconcile_status ?? 'pending';
    $data['status']           = $data['status'] ?? $transaction->status ?? 'pending';
    $data['is_transfer']      = $data['is_transfer'] ?? 0;

    $transaction->update($data);

    return redirect()
        ->route('transactions.index', $account->id)
        ->with('success', "Transaction #{$transaction->id} updated.");
}
    // app/Http/Controllers/BankTransactionController.php

public function show(int $accountId, int $transactionId)
{
    $account     = $this->findAccount($accountId);
    $transaction = $this->findTransaction($accountId, $transactionId);

    return view('transactions.show', compact('account', 'transaction'));
}

    /**
     * Mark transaction as excluded (ignored from books).
     */
    public function markExcluded(int $accountId, int $transactionId)
    {
        $account     = $this->findAccount($accountId);
        $transaction = $this->findTransaction($accountId, $transactionId);

        $transaction->update([
            'status'    => 'excluded',
            'posted_at' => null,
        ]);

        return redirect()
            ->route('transactions.index', $account->id)
            ->with('success', "Transaction #{$transaction->id} excluded.");
    }

    /**
     * Restore transaction back to pending.
     */
    public function restorePending(int $accountId, int $transactionId)
    {
        $account     = $this->findAccount($accountId);
        $transaction = $this->findTransaction($accountId, $transactionId);

        $transaction->update([
            'status'    => 'pending',
            'posted_at' => null,
        ]);

        return redirect()
            ->route('transactions.index', $account->id)
            ->with('success', "Transaction #{$transaction->id} restored to pending.");
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function findAccount(int $accountId): BankAccount
    {
        return BankAccount::findOrFail($accountId);
    }

    protected function findTransaction(int $accountId, int $transactionId): BankTransaction
    {
        return BankTransaction::where('bank_account_id', $accountId)
            ->where('id', $transactionId)
            ->firstOrFail();
    }
}