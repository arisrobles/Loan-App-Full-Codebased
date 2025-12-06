<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ChartOfAccount;

class AdminSettingsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $roles = Role::orderBy('id')->get();
            if ($roles->isEmpty()) {
                return redirect()->route('dashboard.index')
                    ->with('error', 'No roles found. Please seed roles first.');
            }

            $selectedRole = $request->query('role', $roles->first()->slug);

            $permissions = Permission::orderBy('group')->orderBy('id')->get();

            // Matrix: [permission_key => [role_slug => allowed(bool)]]
            $matrix = [];
            foreach ($permissions as $perm) {
                $matrix[$perm->key] = [];
                foreach ($roles as $role) {
                    $matrix[$perm->key][$role->slug] = (bool) $role->permissions()
                        ->where('permissions.id', $perm->id)
                        ->wherePivot('allowed', 1)
                        ->exists();
                }
            }

            $coa = ChartOfAccount::orderBy('code')->get();

            return view('settings', compact('roles', 'permissions', 'matrix', 'coa', 'selectedRole'));
        } catch (\Exception $e) {
            \Log::error('Admin settings error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('dashboard.index')
                ->with('error', 'Error loading settings: ' . $e->getMessage());
        }
    }
}
