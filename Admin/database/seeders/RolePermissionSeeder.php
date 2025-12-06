<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            ['name' => 'Administrator', 'slug' => 'admin'],
            ['name' => 'Manager', 'slug' => 'manager'],
            ['name' => 'Staff', 'slug' => 'staff'],
            ['name' => 'Viewer', 'slug' => 'viewer'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                ['name' => $roleData['name']]
            );
        }

        // Create permissions grouped by feature
        $permissions = [
            // Loan Management
            ['name' => 'View Loans', 'key' => 'loans.view', 'group' => 'Loans'],
            ['name' => 'Create Loans', 'key' => 'loans.create', 'group' => 'Loans'],
            ['name' => 'Edit Loans', 'key' => 'loans.edit', 'group' => 'Loans'],
            ['name' => 'Delete Loans', 'key' => 'loans.delete', 'group' => 'Loans'],
            ['name' => 'Approve Loans', 'key' => 'loans.approve', 'group' => 'Loans'],
            ['name' => 'Disburse Loans', 'key' => 'loans.disburse', 'group' => 'Loans'],

            // Borrower Management
            ['name' => 'View Borrowers', 'key' => 'borrowers.view', 'group' => 'Borrowers'],
            ['name' => 'Create Borrowers', 'key' => 'borrowers.create', 'group' => 'Borrowers'],
            ['name' => 'Edit Borrowers', 'key' => 'borrowers.edit', 'group' => 'Borrowers'],
            ['name' => 'Delete Borrowers', 'key' => 'borrowers.delete', 'group' => 'Borrowers'],

            // Payment Management
            ['name' => 'View Payments', 'key' => 'payments.view', 'group' => 'Payments'],
            ['name' => 'Approve Payments', 'key' => 'payments.approve', 'group' => 'Payments'],
            ['name' => 'Reject Payments', 'key' => 'payments.reject', 'group' => 'Payments'],
            ['name' => 'Record Payments', 'key' => 'payments.record', 'group' => 'Payments'],

            // Reports
            ['name' => 'View Reports', 'key' => 'reports.view', 'group' => 'Reports'],
            ['name' => 'Export Reports', 'key' => 'reports.export', 'group' => 'Reports'],

            // Settings
            ['name' => 'Manage Settings', 'key' => 'settings.manage', 'group' => 'Settings'],
            ['name' => 'Manage Roles', 'key' => 'settings.roles', 'group' => 'Settings'],
            ['name' => 'Manage Chart of Accounts', 'key' => 'settings.coa', 'group' => 'Settings'],

            // Bank Transactions
            ['name' => 'View Bank Transactions', 'key' => 'bank.view', 'group' => 'Bank'],
            ['name' => 'Create Bank Transactions', 'key' => 'bank.create', 'group' => 'Bank'],
            ['name' => 'Edit Bank Transactions', 'key' => 'bank.edit', 'group' => 'Bank'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(
                ['key' => $permData['key']],
                [
                    'name' => $permData['name'],
                    'group' => $permData['group'],
                ]
            );
        }

        // Assign all permissions to admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $allPermissions = Permission::all();
            $syncData = [];
            foreach ($allPermissions as $perm) {
                $syncData[$perm->id] = ['allowed' => 1];
            }
            $adminRole->permissions()->sync($syncData);
        }

        // Assign admin user to admin role if exists and has no role
        $adminUser = User::where('username', 'admin')->first();
        if ($adminUser && !$adminUser->role_id && $adminRole) {
            $adminUser->update(['role_id' => $adminRole->id]);
        }

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Admin user assigned to admin role (if exists).');
    }
}
