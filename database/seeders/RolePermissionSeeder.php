<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Expedients
            'expedients.view',
            'expedients.create',
            'expedients.update',
            'expedients.delete',
            'expedients.change-location',

            // Loans
            'loans.view',
            'loans.create',
            'loans.approve',
            'loans.deliver',
            'loans.return',
            'loans.cancel-own',
            'loans.cancel-any',

            // Employees
            'employees.view',
            'employees.sync',

            // Locations
            'locations.view',
            'locations.create',
            'locations.update',
            'locations.delete',

            // Movements
            'movements.view',

            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Dashboard
            'dashboard.view',

            // Settings
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superuser = Role::firstOrCreate(['name' => 'superuser']);
        $superuser->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'expedients.view', 'expedients.create', 'expedients.update', 'expedients.delete',
            'expedients.change-location',
            'loans.view', 'loans.create', 'loans.approve', 'loans.deliver', 'loans.return',
            'loans.cancel-own', 'loans.cancel-any',
            'employees.view', 'employees.sync',
            'locations.view', 'locations.create', 'locations.update', 'locations.delete',
            'movements.view',
            'dashboard.view',
        ]);

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo([
            'expedients.view',
            'loans.view', 'loans.create', 'loans.cancel-own',
            'employees.view',
            'locations.view',
            'movements.view',
            'dashboard.view',
        ]);
    }
}
