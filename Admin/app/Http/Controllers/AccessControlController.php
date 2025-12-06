<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccessControlController extends Controller
{
    /**
     * Handles a normal form POST (no JSON).
     * Expects:
     *  - role_slug
     *  - permissions[key] = "1" (checkboxes present when checked)
     */
    public function updateRolePermissions(Request $request)
    {
        $data = $request->validate([
            'role_slug'   => ['required', Rule::exists('roles', 'slug')],
            'permissions' => ['array'], // may be empty / missing when all unchecked
        ]);

        $role = Role::where('slug', $data['role_slug'])->firstOrFail();

        // Build full sync payload for ALL permissions so unchecked become allowed=0
        $allPerms = Permission::pluck('id', 'key'); // [key => id]
        $submitted = (array) ($data['permissions'] ?? []);

        $sync = [];
        foreach ($allPerms as $key => $permId) {
            $sync[$permId] = ['allowed' => isset($submitted[$key]) ? 1 : 0];
        }

        // Overwrite the whole set for this role
        $role->permissions()->sync($sync);

        return redirect()
            ->route('admin.settings', ['role' => $role->slug])
            ->with('success', 'Permissions updated for role: ' . $role->slug);
    }
}
