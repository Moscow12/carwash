<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Others
            [
                'category' => 'Others',
                'permissions' => [
                    ['name' => 'export_data', 'display_name' => 'View export to buttons (csv/excel/print/pdf) on tables', 'sort_order' => 1],
                    ['name' => 'view_dashboard', 'display_name' => 'View dashboard', 'sort_order' => 2],
                    ['name' => 'view_reports', 'display_name' => 'View reports', 'sort_order' => 3],
                ],
            ],

            // User Management
            [
                'category' => 'User',
                'permissions' => [
                    ['name' => 'view_user', 'display_name' => 'View user', 'sort_order' => 1],
                    ['name' => 'add_user', 'display_name' => 'Add user', 'sort_order' => 2],
                    ['name' => 'edit_user', 'display_name' => 'Edit user', 'sort_order' => 3],
                    ['name' => 'delete_user', 'display_name' => 'Delete user', 'sort_order' => 4],
                ],
            ],

            // Role Management
            [
                'category' => 'Roles',
                'permissions' => [
                    ['name' => 'view_role', 'display_name' => 'View role', 'sort_order' => 1],
                    ['name' => 'add_role', 'display_name' => 'Add Role', 'sort_order' => 2],
                    ['name' => 'edit_role', 'display_name' => 'Edit Role', 'sort_order' => 3],
                    ['name' => 'delete_role', 'display_name' => 'Delete role', 'sort_order' => 4],
                ],
            ],

            // Staff Management
            [
                'category' => 'Staff',
                'permissions' => [
                    ['name' => 'view_staff', 'display_name' => 'View staff', 'sort_order' => 1],
                    ['name' => 'add_staff', 'display_name' => 'Add staff', 'sort_order' => 2],
                    ['name' => 'edit_staff', 'display_name' => 'Edit staff', 'sort_order' => 3],
                    ['name' => 'delete_staff', 'display_name' => 'Delete staff', 'sort_order' => 4],
                ],
            ],

            // Supplier Management
            [
                'category' => 'Supplier',
                'permissions' => [
                    ['name' => 'view_all_supplier', 'display_name' => 'View all supplier', 'sort_order' => 1],
                    ['name' => 'view_own_supplier', 'display_name' => 'View own supplier', 'sort_order' => 2],
                    ['name' => 'add_supplier', 'display_name' => 'Add supplier', 'sort_order' => 3],
                    ['name' => 'edit_supplier', 'display_name' => 'Edit supplier', 'sort_order' => 4],
                    ['name' => 'delete_supplier', 'display_name' => 'Delete supplier', 'sort_order' => 5],
                ],
            ],

            // Customer Management
            [
                'category' => 'Customer',
                'permissions' => [
                    ['name' => 'view_customer', 'display_name' => 'View customer', 'sort_order' => 1],
                    ['name' => 'add_customer', 'display_name' => 'Add customer', 'sort_order' => 2],
                    ['name' => 'edit_customer', 'display_name' => 'Edit customer', 'sort_order' => 3],
                    ['name' => 'delete_customer', 'display_name' => 'Delete customer', 'sort_order' => 4],
                ],
            ],

            // Item/Product Management
            [
                'category' => 'Items',
                'permissions' => [
                    ['name' => 'view_item', 'display_name' => 'View item', 'sort_order' => 1],
                    ['name' => 'add_item', 'display_name' => 'Add item', 'sort_order' => 2],
                    ['name' => 'edit_item', 'display_name' => 'Edit item', 'sort_order' => 3],
                    ['name' => 'delete_item', 'display_name' => 'Delete item', 'sort_order' => 4],
                ],
            ],

            // Sales Management
            [
                'category' => 'Sales',
                'permissions' => [
                    ['name' => 'view_sale', 'display_name' => 'View sales', 'sort_order' => 1],
                    ['name' => 'create_sale', 'display_name' => 'Create sale', 'sort_order' => 2],
                    ['name' => 'edit_sale', 'display_name' => 'Edit sale', 'sort_order' => 3],
                    ['name' => 'void_sale', 'display_name' => 'Void sale', 'sort_order' => 4],
                    ['name' => 'delete_sale', 'display_name' => 'Delete sale', 'sort_order' => 5],
                ],
            ],

            // Purchase Management
            [
                'category' => 'Purchases',
                'permissions' => [
                    ['name' => 'view_purchase', 'display_name' => 'View purchase', 'sort_order' => 1],
                    ['name' => 'create_purchase', 'display_name' => 'Create purchase', 'sort_order' => 2],
                    ['name' => 'edit_purchase', 'display_name' => 'Edit purchase', 'sort_order' => 3],
                    ['name' => 'delete_purchase', 'display_name' => 'Delete purchase', 'sort_order' => 4],
                    ['name' => 'approve_purchase', 'display_name' => 'Approve purchase', 'sort_order' => 5],
                ],
            ],

            // Inventory/Stock Management
            [
                'category' => 'Inventory',
                'permissions' => [
                    ['name' => 'view_inventory', 'display_name' => 'View inventory', 'sort_order' => 1],
                    ['name' => 'adjust_inventory', 'display_name' => 'Adjust inventory', 'sort_order' => 2],
                    ['name' => 'stocktaking', 'display_name' => 'Perform stocktaking', 'sort_order' => 3],
                    ['name' => 'transfer_stock', 'display_name' => 'Transfer stock', 'sort_order' => 4],
                ],
            ],

            // Expense Management
            [
                'category' => 'Expenses',
                'permissions' => [
                    ['name' => 'view_expense', 'display_name' => 'View expense', 'sort_order' => 1],
                    ['name' => 'add_expense', 'display_name' => 'Add expense', 'sort_order' => 2],
                    ['name' => 'edit_expense', 'display_name' => 'Edit expense', 'sort_order' => 3],
                    ['name' => 'delete_expense', 'display_name' => 'Delete expense', 'sort_order' => 4],
                    ['name' => 'approve_expense', 'display_name' => 'Approve expense', 'sort_order' => 5],
                ],
            ],

            // Business Settings
            [
                'category' => 'Settings',
                'permissions' => [
                    ['name' => 'view_settings', 'display_name' => 'View settings', 'sort_order' => 1],
                    ['name' => 'edit_settings', 'display_name' => 'Edit settings', 'sort_order' => 2],
                    ['name' => 'manage_business', 'display_name' => 'Manage business', 'sort_order' => 3],
                ],
            ],

            // Restaurant/POS
            [
                'category' => 'Restaurant',
                'permissions' => [
                    ['name' => 'access_pos', 'display_name' => 'Access POS', 'sort_order' => 1],
                    ['name' => 'view_orders', 'display_name' => 'View orders', 'sort_order' => 2],
                    ['name' => 'create_order', 'display_name' => 'Create order', 'sort_order' => 3],
                    ['name' => 'edit_order', 'display_name' => 'Edit order', 'sort_order' => 4],
                    ['name' => 'void_order', 'display_name' => 'Void order', 'sort_order' => 5],
                    ['name' => 'manage_tables', 'display_name' => 'Manage tables', 'sort_order' => 6],
                ],
            ],

            // Hotel Management
            [
                'category' => 'Hotel',
                'permissions' => [
                    ['name' => 'view_reservations', 'display_name' => 'View reservations', 'sort_order' => 1],
                    ['name' => 'create_reservation', 'display_name' => 'Create reservation', 'sort_order' => 2],
                    ['name' => 'checkin', 'display_name' => 'Check-in guests', 'sort_order' => 3],
                    ['name' => 'checkout', 'display_name' => 'Check-out guests', 'sort_order' => 4],
                    ['name' => 'manage_rooms', 'display_name' => 'Manage rooms', 'sort_order' => 5],
                    ['name' => 'housekeeping', 'display_name' => 'Housekeeping access', 'sort_order' => 6],
                ],
            ],
        ];

        foreach ($permissions as $categoryData) {
            foreach ($categoryData['permissions'] as $permData) {
                Permission::updateOrCreate(
                    ['name' => $permData['name']],
                    [
                        'display_name' => $permData['display_name'],
                        'category' => $categoryData['category'],
                        'description' => $permData['description'] ?? null,
                        'sort_order' => $permData['sort_order'],
                    ]
                );
            }
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
