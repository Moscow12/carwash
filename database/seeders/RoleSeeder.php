<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\UserBusinessRole;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business = Business::first();

        if (!$business) {
            $this->command->warn('Please seed businesses first!');
            return;
        }

        $outlets = PosOutlet::where('business_id', $business->id)->get();

        if ($outlets->isEmpty()) {
            $this->command->warn('No outlets found! Creating test users without outlet assignment.');
        }

        // Define test users with their roles
        $testUsers = [
            [
                'name' => 'John Owner',
                'email' => 'owner@test.com',
                'phone' => '+1234567890',
                'role' => 'owner',
                'user_role' => 'owner',
            ],
            [
                'name' => 'Jane Manager',
                'email' => 'manager@test.com',
                'phone' => '+1234567891',
                'role' => 'manager',
                'user_role' => 'staff',
            ],
            [
                'name' => 'Mike Cashier',
                'email' => 'cashier@test.com',
                'phone' => '+1234567892',
                'role' => 'cashier',
                'user_role' => 'staff',
            ],
            [
                'name' => 'Sarah Waiter',
                'email' => 'waiter@test.com',
                'phone' => '+1234567893',
                'role' => 'waiter',
                'user_role' => 'staff',
            ],
            [
                'name' => 'Tom Bartender',
                'email' => 'bartender@test.com',
                'phone' => '+1234567894',
                'role' => 'bartender',
                'user_role' => 'staff',
            ],
            [
                'name' => 'Lisa Receptionist',
                'email' => 'receptionist@test.com',
                'phone' => '+1234567895',
                'role' => 'receptionist',
                'user_role' => 'staff',
            ],
        ];

        foreach ($testUsers as $userData) {
            // Create or find user
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'role' => $userData['user_role'],
                    'status' => 'active',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign business role
            $roleData = [
                'user_id' => $user->id,
                'business_id' => $business->id,
                'role' => $userData['role'],
                'is_active' => true,
            ];

            // If outlets exist, distribute users across outlets
            if ($outlets->isNotEmpty()) {
                // Assign to first outlet for simplicity, or distribute if needed
                $outlet = $outlets->first();
                $roleData['outlet_id'] = $outlet->id;
            }

            UserBusinessRole::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'business_id' => $business->id,
                ],
                $roleData
            );

            $this->command->info("Created/Updated user: {$userData['name']} with role: {$userData['role']}");
        }

        // Create additional staff records for waiters and bartenders
        $staffPositions = [
            ['email' => 'waiter@test.com', 'position' => 'Waiter'],
            ['email' => 'bartender@test.com', 'position' => 'Bartender'],
            ['email' => 'receptionist@test.com', 'position' => 'Receptionist'],
            ['email' => 'cashier@test.com', 'position' => 'Cashier'],
        ];

        foreach ($staffPositions as $staffData) {
            $user = User::where('email', $staffData['email'])->first();
            if ($user) {
                $existingStaff = \App\Models\staffs::where('user_id', $user->id)
                    ->where('business_id', $business->id)
                    ->exists();

                if (!$existingStaff) {
                    \App\Models\staffs::create([
                        'name' => $user->name,
                        'position' => $staffData['position'],
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'payment_mode' => 'hourly',
                        'commission_type' => 'percentage',
                        'amount' => '15.00',
                        'status' => 'active',
                        'business_id' => $business->id,
                        'user_id' => $user->id,
                    ]);

                    $this->command->info("Created staff record for: {$user->name}");
                }
            }
        }

        $this->command->info('Role seeding completed! Test users credentials:');
        $this->command->info('Email: owner@test.com / manager@test.com / cashier@test.com / etc.');
        $this->command->info('Password: password123');
    }
}
