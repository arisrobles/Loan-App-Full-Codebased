<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BorrowerController extends Controller
{
    private const STATUSES = ['active','inactive','delinquent','closed','blacklisted'];

   
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;
        $page    = max((int) $request->integer('page', 1), 1);
        $offset  = ($page - 1) * $perPage;

        $wheres   = ["deleted_at IS NULL"];
        $bindings = [];

     
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $wheres[]  = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR reference_no LIKE ? OR address LIKE ?)";
            $bindings = array_merge($bindings, ["%$q%","%$q%","%$q%","%$q%","%$q%"]);
        }

       
        if ($request->filled('archived')) {
            $arch = filter_var($request->input('archived'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($arch !== null) {
                $wheres[]  = "is_archived = ?";
                $bindings[] = $arch ? 1 : 0;
            }
        }

        // Status (comma list)
        if ($request->filled('status')) {
            $statuses = array_intersect(
                array_map('trim', explode(',', $request->input('status'))),
                self::STATUSES
            );
            if (!empty($statuses)) {
                $wheres[] = "status IN (".implode(',', array_fill(0, count($statuses), '?')).")";
                $bindings = array_merge($bindings, $statuses);
            }
        }

        // Ranges
        if ($request->filled('min_income')) { $wheres[] = "monthly_income >= ?"; $bindings[] = (float) $request->input('min_income'); }
        if ($request->filled('max_income')) { $wheres[] = "monthly_income <= ?"; $bindings[] = (float) $request->input('max_income'); }
        if ($request->filled('date_from'))  { $wheres[] = "DATE(created_at) >= ?"; $bindings[] = $request->input('date_from'); }
        if ($request->filled('date_to'))    { $wheres[] = "DATE(created_at) <= ?"; $bindings[] = $request->input('date_to'); }

        // Sorting (whitelist)
        $allowedSort = ['created_at','full_name','status','monthly_income'];
        $sortBy = in_array($request->input('sort_by'), $allowedSort, true) ? $request->input('sort_by') : 'created_at';
        $sortDir = strtolower($request->input('sort_dir')) === 'asc' ? 'ASC' : 'DESC';

        $base = "FROM borrowers WHERE ".implode(' AND ', $wheres);

        $total = (int) (DB::selectOne("SELECT COUNT(*) as cnt $base", $bindings)->cnt ?? 0);

        $rows = DB::select(
            "SELECT id, full_name, email, phone, address, status, is_archived, archived_at, created_at, updated_at
             $base ORDER BY $sortBy $sortDir LIMIT ? OFFSET ?",
            array_merge($bindings, [$perPage, $offset])
        );

        // Very simple pagination meta for the view
        $meta = [
            'page'      => $page,
            'per_page'  => $perPage,
            'total'     => $total,
            'last_page' => max((int) ceil($total / max($perPage, 1)), 1),
            // keep current filters for links
            'query'     => $request->query(),
        ];

        return view('borrower', compact('rows', 'meta'));
    }

    /** Create (redirect back with flash) */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $now = now()->format('Y-m-d H:i:s');

        DB::insert(
            "INSERT INTO borrowers
             (full_name,email,phone,address,sex,occupation,birthday,monthly_income,civil_status,reference_no,status,is_archived,archived_at,created_at,updated_at,deleted_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NULL)",
            [
                $data['full_name'],
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['sex'] ?? null,
                $data['occupation'] ?? null,
                $data['birthday'] ?? null,
                $data['monthly_income'] ?? null,
                $data['civil_status'] ?? null,
                $data['reference_no'] ?? null,
                $data['status'] ?? 'active',
                0,
                null,
                $now, $now
            ]
        );

        return redirect()->route('borrowers.index')
            ->with('success', 'Borrower created successfully.');
    }

    /** Update (redirect back with flash) */
    public function update(Request $request, int $id)
    {
        $data = $this->validateData($request, $id);
        $now  = now()->format('Y-m-d H:i:s');

        $fields = [
            'full_name'      => $data['full_name'],
            'email'          => $data['email'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'address'        => $data['address'] ?? null,
            'sex'            => $data['sex'] ?? null,
            'occupation'     => $data['occupation'] ?? null,
            'birthday'       => $data['birthday'] ?? null,
            'monthly_income' => $data['monthly_income'] ?? null,
            'civil_status'   => $data['civil_status'] ?? null,
            'reference_no'   => $data['reference_no'] ?? null,
            'status'         => $data['status'] ?? 'active',
            'updated_at'     => $now,
        ];

        $setSql   = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
        $bindings = array_values($fields);
        $bindings[] = $id;

        DB::update("UPDATE borrowers SET $setSql WHERE id = ? AND deleted_at IS NULL", $bindings);

        return redirect()->back()->with('success', 'Borrower updated.');
    }

    /** Archive (redirect back) */
    public function archive(int $id)
    {
        $now = now()->format('Y-m-d H:i:s');
        DB::update(
            "UPDATE borrowers SET is_archived = 1, archived_at = ?, updated_at = ? WHERE id = ? AND deleted_at IS NULL",
            [$now, $now, $id]
        );

        return redirect()->back()->with('success', 'Borrower archived.');
    }

    /** Unarchive (redirect back) */
    public function unarchive(int $id)
    {
        $now = now()->format('Y-m-d H:i:s');
        DB::update(
            "UPDATE borrowers SET is_archived = 0, archived_at = NULL, updated_at = ? WHERE id = ? AND deleted_at IS NULL",
            [$now, $id]
        );

        return redirect()->back()->with('success', 'Borrower unarchived.');
    }

    /** Update status (redirect back) */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        DB::update(
            "UPDATE borrowers SET status = ?, updated_at = ? WHERE id = ? AND deleted_at IS NULL",
            [$request->input('status'), now()->format('Y-m-d H:i:s'), $id]
        );

        return redirect()->back()->with('success', 'Status updated.');
    }

    /** Soft delete (redirect back) */
    public function destroy(int $id)
    {
        DB::update(
            "UPDATE borrowers SET deleted_at = ? WHERE id = ? AND deleted_at IS NULL",
            [now()->format('Y-m-d H:i:s'), $id]
        );

        return redirect()->back()->with('success', 'Borrower deleted (soft).');
    }

    /** Force delete (redirect back) */
    public function forceDestroy(int $id)
    {
        DB::delete("DELETE FROM borrowers WHERE id = ?", [$id]);
        return redirect()->route('borrowers.index')->with('success', 'Borrower permanently deleted.');
    }

    /** Validation (still use Laravel’s validator) */
    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'full_name'      => ['required', 'string', 'max:255'],
            'email'          => ['nullable','email','max:255', Rule::unique('borrowers','email')->ignore($id)],
            'phone'          => ['nullable','string','max:32'],
            'address'        => ['nullable','string','max:255'],
            'sex'            => ['nullable', Rule::in(['Male','Female','Prefer not to say'])],
            'occupation'     => ['nullable','string','max:255'],
            'birthday'       => ['nullable','date'],
            'monthly_income' => ['nullable','numeric','min:0'],
            'civil_status'   => ['nullable','string','max:64'],
            'reference_no'   => ['nullable','string','max:128'],
            'status'         => ['nullable', Rule::in(self::STATUSES)],
        ]);
    }
}
