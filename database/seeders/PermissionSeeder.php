<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Spatie Cache clear karein
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Permissions ki exact list jo aapne Blade mein use ki hai
        $permissions = [
            // Banks
            'view-banks',
            //company information
            'view-Company-Information',

            // Cash
            'view-cash',

            // Purchase / Vendors
            'view-purchases-list',
            'add-purchaser',
            'create-purchase-bill',
            'view-purchase-invoices',

            // Sales / Customers
            'view-customers-list',
            'add-customer',
            'create-sales-invoice',
            'view-sales-invoices',

            // Quotations
            'view-quotations',

            // Stock
            'view-stock',
            'add-stock',

            // Finances
            'view-profits',
            'view-expenses',
            'view-daybooks',

            // General Transactions
            'view-transaction-overview',
            'transaction-customer-to-vendor',
            'transaction-bank-to-bank',
            'transaction-bank-withdraw',
            'transaction-bank-deposit',

            // Access Control
            'manage-roles',
            'manage-users-permissions',
        ];

        // 2. Permissions create karein
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // 3. Super Admin Role
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        // 4. Admin Role (Isay saari permissions attach kar dete hain fallback ke liye)
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissions);

        // 5. User ko Role Assign karein
        $user = User::where('email', 'atif@syedfoodimpex.com')->first();
        if ($user) {
            $user->assignRole($superAdminRole);
            $this->command->info('Success: atif@syedfoodimpex.com is now Super Admin.');
        } else {
            $this->command->error('Error: User with email atif@syedfoodimpex.com not found!');
        }

        $this->command->info('Permissions and Roles synced with Blade successfully!');
    }
}
