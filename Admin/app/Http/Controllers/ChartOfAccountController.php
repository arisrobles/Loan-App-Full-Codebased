<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    /** List + filter */
    public function index(Request $request)
    {
        $filters = $request->all();

        $rows = ChartOfAccount::query()
            ->filter($filters)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        $meta = [
            'page'      => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
            'per_page'  => $rows->perPage(),
            'total'     => $rows->total(),
        ];

        return view('chart-of-accounts.index', [
            'rows'       => $rows,
            'meta'       => $meta,
            'filters'    => $filters,
            'reports'    => ChartOfAccount::REPORTS,
            'groups'     => ChartOfAccount::GROUPS,
        ]);
    }

    /** Show create form */
    public function create()
    {
        return view('chart-of-accounts.create', [
            'account'      => new ChartOfAccount(),
            'reports'      => ChartOfAccount::REPORTS,
            'groups'       => ChartOfAccount::GROUPS,
            'normalBalances' => ChartOfAccount::NORMAL_BALANCES,
            'effects'      => ChartOfAccount::EFFECTS,
        ]);
    }

    /** Store new */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        ChartOfAccount::create($data);

        // Redirect back to the referring page (settings or index)
        return redirect()
            ->back()
            ->with('success', 'Account created successfully.');
    }

    /** Show edit form */
    public function edit(ChartOfAccount $chartOfAccount)
    {
        return view('chart-of-accounts.edit', [
            'account'        => $chartOfAccount,
            'reports'        => ChartOfAccount::REPORTS,
            'groups'         => ChartOfAccount::GROUPS,
            'normalBalances' => ChartOfAccount::NORMAL_BALANCES,
            'effects'        => ChartOfAccount::EFFECTS,
        ]);
    }

    /** Update */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $data = $this->validateData($request, $chartOfAccount->id);

        $chartOfAccount->update($data);

        // Redirect back to the referring page (settings or index)
        return redirect()
            ->back()
            ->with('success', 'Account updated successfully.');
    }

    /** Delete (hard delete) */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->delete();

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Account deleted.');
    }

    /** Export Chart of Accounts to CSV */
    public function export()
    {
        $accounts = ChartOfAccount::orderBy('code')->get();

        $filename = 'chart_of_accounts_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($accounts) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, ['Code', 'Name', 'Description', 'Report', 'Group Account', 'Normal Balance', 'Debit Effect', 'Credit Effect', 'Is Active', 'Sort Order']);

            // CSV data
            foreach ($accounts as $account) {
                fputcsv($file, [
                    $account->code,
                    $account->name,
                    $account->description ?? '',
                    $account->report ?? '',
                    $account->group_account ?? '',
                    $account->normal_balance ?? '',
                    $account->debit_effect ?? '',
                    $account->credit_effect ?? '',
                    $account->is_active ? '1' : '0',
                    $account->sort_order ?? '0',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Import Chart of Accounts from CSV */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // 5MB max
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $dryRun = $request->boolean('dry_run', false);

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->withErrors('Could not read CSV file.');
        }

        // Skip header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->withErrors('CSV file is empty or invalid.');
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue; // Skip invalid rows

            $data = [
                'code' => trim($row[0] ?? ''),
                'name' => trim($row[1] ?? ''),
                'description' => trim($row[2] ?? ''),
                'report' => trim($row[3] ?? ''),
                'group_account' => trim($row[4] ?? ''),
                'normal_balance' => trim($row[5] ?? ''),
                'debit_effect' => trim($row[6] ?? ''),
                'credit_effect' => trim($row[7] ?? ''),
                'is_active' => isset($row[8]) ? (bool)trim($row[8]) : true,
                'sort_order' => isset($row[9]) ? (int)trim($row[9]) : 0,
            ];

            if (empty($data['code']) || empty($data['name'])) {
                $errors[] = "Skipped row: Missing code or name";
                continue;
            }

            try {
                if (!$dryRun) {
                    $account = ChartOfAccount::updateOrCreate(
                        ['code' => $data['code']],
                        $data
                    );

                    if ($account->wasRecentlyCreated) {
                        $imported++;
                    } else {
                        $updated++;
                    }
                } else {
                    // Dry run - just validate
                    $existing = ChartOfAccount::where('code', $data['code'])->exists();
                    if ($existing) {
                        $updated++;
                    } else {
                        $imported++;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Error processing {$data['code']}: " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = $dryRun
            ? "Dry run: Would import {$imported} new, update {$updated} existing accounts."
            : "Imported {$imported} new accounts, updated {$updated} existing accounts.";

        if (!empty($errors)) {
            $message .= " " . count($errors) . " errors occurred.";
        }

        return back()->with('success', $message)->with('import_errors', $errors);
    }

    /** Merge two chart of accounts */
    public function merge(Request $request)
    {
        $request->validate([
            'source_id' => ['required', 'exists:chart_of_accounts,id'],
            'target_id' => ['required', 'exists:chart_of_accounts,id', 'different:source_id'],
        ]);

        $source = ChartOfAccount::findOrFail($request->source_id);
        $target = ChartOfAccount::findOrFail($request->target_id);

        // Archive the source account
        $source->update(['is_active' => false]);

        // Note: In a real implementation, you might want to:
        // - Update all references from source to target in bank_transactions
        // - Move transactions/balances
        // - Delete the source account after migration
        // For now, we just archive it

        return redirect()
            ->back()
            ->with('success', "Account {$source->code} has been archived. Merge functionality is basic - manual data migration may be required.");
    }

    /** Common validation rules */
    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('chart_of_accounts', 'code')->ignore($id),
            ],
            'name' => [
                'required',
                'string',
                'max:128',
            ],
            'description'   => ['nullable', 'string', 'max:512'],
            'report'        => ['required', Rule::in(ChartOfAccount::REPORTS)],
            'group_account' => ['required', Rule::in(ChartOfAccount::GROUPS)],
            'normal_balance'=> ['nullable', Rule::in(ChartOfAccount::NORMAL_BALANCES)],
            'debit_effect'  => ['nullable', Rule::in(ChartOfAccount::EFFECTS)],
            'credit_effect' => ['nullable', Rule::in(ChartOfAccount::EFFECTS)],
            'is_active'     => ['nullable', 'boolean'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
        ], [
            'code.required'   => 'The account code is required.',
            'code.unique'     => 'This account code is already in use.',
            'name.required'   => 'The account name is required.',
        ]) + [
            // Default values
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order', 0),
        ];
    }
}
