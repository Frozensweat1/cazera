<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStaff;
use App\Models\Module;
use App\Models\ModuleStaff;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'pos.create_order',
            'pos.edit_order',
            'pos.view_orders',
            'pos.delete_order',
            'pos.process_payment',
            'kitchen.view_orders',
            'kitchen.update_status',
            'inventory.view',
            'inventory.manage',
            'inventory.stock_in',
            'inventory.stock_out',
            'expenses.create',
            'expenses.edit',
            'expenses.view',
            'expenses.approve',
            'expenses.delete',
            'reports.view',
            'production_day.open',
            'production_day.close',
            'production_day.lock',
            'settings.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $rolePermissions = [
            'Super Admin' => $permissions,
            'Branch Manager' => array_values(array_diff($permissions, ['settings.manage'])),
            'POS Operator' => [
                'pos.create_order',
                'pos.edit_order',
                'pos.view_orders',
                'pos.process_payment',
            ],
            'Kitchen Staff' => [
                'kitchen.view_orders',
                'kitchen.update_status',
            ],
            'Delivery Staff' => [
                'pos.view_orders',
            ],
            'Inventory Manager' => [
                'inventory.view',
                'inventory.manage',
                'inventory.stock_in',
                'inventory.stock_out',
            ],
            'Accountant' => [
                'expenses.view',
                'expenses.create',
                'expenses.edit',
                'expenses.approve',
                'expenses.delete',
                'reports.view',
            ],
        ];



        // Create branches
        $branches = collect([
            [
                'name' => 'Main Branch',
                'slug' => 'main-branch',
                'location' => 'Head Office',
                'phone' => '08000000000',
                'email' => 'main@thyfood.test',
                'latitude' => 6.5244,
                'longitude' => 3.3792,
                'is_active' => true,
                'settings' => json_encode([
                    'operating_hours' => '9:00-22:00',
                    'tax_rate' => 7.5,
                    'delivery_radius' => 10,
                ]),
            ],
            [
                'name' => 'East Branch',
                'slug' => 'east-branch',
                'location' => 'East Business Park',
                'phone' => '08011112222',
                'email' => 'east@thyfood.test',
                'latitude' => 6.5300,
                'longitude' => 3.3800,
                'is_active' => true,
                'settings' => json_encode([
                    'operating_hours' => '8:00-23:00',
                    'tax_rate' => 7.5,
                    'delivery_radius' => 12,
                ]),
            ],
            [
                'name' => 'West Branch',
                'slug' => 'west-branch',
                'location' => 'West Mall',
                'phone' => '08022223333',
                'email' => 'west@thyfood.test',
                'latitude' => 6.5200,
                'longitude' => 3.3600,
                'is_active' => true,
                'settings' => json_encode([
                    'operating_hours' => '10:00-22:00',
                    'tax_rate' => 7.5,
                    'delivery_radius' => 8,
                ]),
            ],
            [
                'name' => 'North Branch',
                'slug' => 'north-branch',
                'location' => 'North Transit Centre',
                'phone' => '08033334444',
                'email' => 'north@thyfood.test',
                'latitude' => 6.5400,
                'longitude' => 3.3900,
                'is_active' => true,
                'settings' => json_encode([
                    'operating_hours' => '9:00-22:00',
                    'tax_rate' => 7.5,
                    'delivery_radius' => 11,
                ]),
            ],
            [
                'name' => 'South Branch',
                'slug' => 'south-branch',
                'location' => 'South Market',
                'phone' => '08044445555',
                'email' => 'south@thyfood.test',
                'latitude' => 6.5100,
                'longitude' => 3.3700,
                'is_active' => true,
                'settings' => json_encode([
                    'operating_hours' => '9:00-23:00',
                    'tax_rate' => 7.5,
                    'delivery_radius' => 9,
                ]),
            ],
        ])->mapWithKeys(function (array $branchData) {
            $branch = Branch::firstOrCreate([
                'slug' => $branchData['slug'],
            ], $branchData);

            return [$branchData['slug'] => $branch];
        });

        $branch = $branches['main-branch'];

        // Create sample modules
        $modules = collect([
            [
                'name' => 'Restaurant',
                'slug' => 'restaurant',
                'type' => 'pos',
                'description' => 'Main dining area for food service',
                'pos_settings' => json_encode([
                    'tax_rate' => 7.5,
                    'service_charge' => 10,
                    'max_tables' => 20,
                ]),
                'settings' => json_encode([
                    'operating_hours' => '11:00-22:00',
                    'capacity' => 100,
                ]),
            ],
            [
                'name' => 'Bar',
                'slug' => 'bar',
                'type' => 'pos',
                'description' => 'Bar and beverage service',
                'pos_settings' => json_encode([
                    'tax_rate' => 7.5,
                    'happy_hour_discount' => 20,
                    'max_capacity' => 50,
                ]),
                'settings' => json_encode([
                    'operating_hours' => '16:00-02:00',
                    'age_restriction' => 18,
                ]),
            ],
            [
                'name' => 'Delivery',
                'slug' => 'delivery',
                'type' => 'pos',
                'description' => 'Delivery and dispatch operations',
                'pos_settings' => json_encode([
                    'tax_rate' => 7.5,
                    'delivery_fee' => 500.00,
                ]),
                'settings' => json_encode([
                    'operating_hours' => '8:00-23:00',
                ]),
            ],
            [
                'name' => 'Bakery',
                'slug' => 'bakery',
                'type' => 'pos',
                'description' => 'Bakery service and pastry counter',
                'pos_settings' => json_encode([
                    'tax_rate' => 7.5,
                    'max_display_items' => 40,
                ]),
                'settings' => json_encode([
                    'operating_hours' => '07:00-20:00',
                ]),
            ],
            [
                'name' => 'Catering',
                'slug' => 'catering',
                'type' => 'pos',
                'description' => 'Catering and event bookings',
                'pos_settings' => json_encode([
                    'tax_rate' => 7.5,
                    'minimum_order' => 5000.00,
                ]),
                'settings' => json_encode([
                    'operating_hours' => '08:00-20:00',
                ]),
            ],
        ])->mapWithKeys(function (array $moduleData) use ($branch) {
            $module = Module::firstOrCreate([
                'branch_id' => $branch->id,
                'slug' => $moduleData['slug'],
            ], array_merge($moduleData, [
                'branch_id' => $branch->id,
                'is_active' => true,
            ]));

            return [$moduleData['slug'] => $module];
        });

        $restaurantModule = $modules['restaurant'];
        $barModule = $modules['bar'];

        foreach ($rolePermissions as $roleName => $assignedPermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions(array_values(array_filter($assignedPermissions)));
        }

        $users = collect([
            [
                'name' => 'Super Admin',
                'email' => 'admin@cazera.test',
                'phone' => '08000000000',
                'password' => Hash::make('password'),
                'roles' => ['Super Admin'],
            ],
            [
                'name' => 'Branch Manager',
                'email' => 'manager@cazera.test',
                'phone' => '08011112222',
                'password' => Hash::make('password'),
                'roles' => ['Branch Manager'],
            ],
            [
                'name' => 'POS Operator',
                'email' => 'pos@cazera.test',
                'phone' => '08022223333',
                'password' => Hash::make('password'),
                'roles' => ['POS Operator'],
            ],
            [
                'name' => 'Kitchen Staff',
                'email' => 'kitchen@cazera.test',
                'phone' => '08033334444',
                'password' => Hash::make('password'),
                'roles' => ['Kitchen Staff'],
            ],
            [
                'name' => 'Accountant',
                'email' => 'accountant@cazera.test',
                'phone' => '08044445555',
                'password' => Hash::make('password'),
                'roles' => ['Accountant'],
            ],
        ])->mapWithKeys(function (array $userData) {
            $roles = $userData['roles'];
            unset($userData['roles']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->syncRoles($roles);

            return [$userData['email'] => $user];
        });

        $adminUser = $users['admin@cazera.test'];
        $managerUser = $users['manager@cazera.test'];
        $posUser = $users['pos@cazera.test'];
        $kitchenUser = $users['kitchen@cazera.test'];
        $accountantUser = $users['accountant@cazera.test'];

        foreach ($branches as $assignedBranch) {
            BranchStaff::updateOrCreate(
                ['user_id' => $adminUser->id, 'branch_id' => $assignedBranch->id],
                ['assigned_by' => $adminUser->id, 'assigned_at' => now(), 'is_active' => true]
            );
        }

        foreach ([$managerUser, $posUser, $kitchenUser, $accountantUser] as $assignedUser) {
            BranchStaff::updateOrCreate(
                ['user_id' => $assignedUser->id, 'branch_id' => $branch->id],
                ['assigned_by' => $adminUser->id, 'assigned_at' => now(), 'is_active' => true]
            );
        }

        foreach ([$restaurantModule, $barModule] as $assignedModule) {
            ModuleStaff::updateOrCreate(
                ['user_id' => $posUser->id, 'module_id' => $assignedModule->id],
                [
                    'branch_id' => $assignedModule->branch_id,
                    'assigned_by' => $adminUser->id,
                    'assigned_at' => now(),
                    'is_active' => true,
                ]
            );
        }

        ModuleStaff::updateOrCreate(
            ['user_id' => $kitchenUser->id, 'module_id' => $restaurantModule->id],
            [
                'branch_id' => $restaurantModule->branch_id,
                'assigned_by' => $adminUser->id,
                'assigned_at' => now(),
                'is_active' => true,
            ]
        );

        $this->call([
            BackofficeDemoDataSeeder::class,
            WebsiteContentSeeder::class,
        ]);
    }
}
