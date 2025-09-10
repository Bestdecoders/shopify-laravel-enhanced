<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Bestdecoders\ShopifyLaravelEnhanced\Models\CouponCode;
use Osiset\ShopifyApp\Storage\Models\Charge;

class SubscriptionTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating subscription test data...');
        
        // Get user model class
        $userModel = config('shopify-enhanced.user_model', \App\Models\User::class);
        
        // Create test users if they don't exist
        $testUsers = [
            [
                'name' => 'Test Shop 1',
                'email' => 'testshop1@example.com',
                'shopify_domain' => 'testshop1.myshopify.com',
                'shopify_token' => 'test_token_1',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Test Shop 2', 
                'email' => 'testshop2@example.com',
                'shopify_domain' => 'testshop2.myshopify.com',
                'shopify_token' => 'test_token_2',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Test Shop 3',
                'email' => 'testshop3@example.com', 
                'shopify_domain' => 'testshop3.myshopify.com',
                'shopify_token' => 'test_token_3',
                'password' => Hash::make('password'),
            ]
        ];

        $createdUsers = [];
        foreach ($testUsers as $userData) {
            $user = $userModel::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $createdUsers[] = $user;
            $this->command->info("Created/found user: {$user->name} (ID: {$user->id})");
        }

        // Create test coupon codes
        $testCoupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10,
                'description' => '10% welcome discount',
                'usage_limit' => 100,
                'used_count' => 0,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
                'applicable_plans' => ['monthly', 'yearly'],
                'minimum_amount' => 0,
            ],
            [
                'code' => 'SAVE25',
                'type' => 'fixed',
                'value' => 25,
                'description' => '$25 off any plan',
                'usage_limit' => 50,
                'used_count' => 5,
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
                'applicable_plans' => ['monthly', 'yearly', 'lifetime'],
                'minimum_amount' => 50,
            ],
            [
                'code' => 'FREETRIAL',
                'type' => 'free_days',
                'value' => 7,
                'description' => '7 days free trial extension',
                'usage_limit' => 20,
                'used_count' => 2,
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
                'applicable_plans' => ['monthly', 'yearly'],
                'minimum_amount' => 0,
            ],
            [
                'code' => 'BLACKFRIDAY',
                'type' => 'percentage',
                'value' => 50,
                'description' => '50% Black Friday special',
                'usage_limit' => 10,
                'used_count' => 8,
                'expires_at' => now()->subDays(1), // Expired
                'is_active' => false,
                'applicable_plans' => ['yearly', 'lifetime'],
                'minimum_amount' => 100,
            ],
            [
                'code' => 'FREEMONTH',
                'type' => 'free_days',
                'value' => 30,
                'description' => 'Free month for loyal customers',
                'usage_limit' => 5,
                'used_count' => 1,
                'expires_at' => now()->addDays(15),
                'is_active' => true,
                'applicable_plans' => ['monthly'],
                'minimum_amount' => 0,
            ]
        ];

        foreach ($testCoupons as $couponData) {
            $coupon = CouponCode::firstOrCreate(
                ['code' => $couponData['code']],
                $couponData
            );
            $this->command->info("Created/found coupon: {$coupon->code} ({$coupon->type})");
        }

        // Create test charges for different scenarios
        $testCharges = [
            // Active monthly subscription for user 1
            [
                'user_id' => $createdUsers[0]->id,
                'charge_id' => 1001,
                'type' => 'recurring',
                'status' => 'active',
                'name' => 'Monthly Plan',
                'price' => 29.99,
                'interval' => 'EVERY_30_DAYS',
                'trial_days' => 14,
                'test' => true,
                'coupon_code' => null,
                'cancelled_on' => null,
                'created_at' => now()->subDays(5),
            ],
            // Cancelled yearly subscription for user 2
            [
                'user_id' => $createdUsers[1]->id,
                'charge_id' => 1002,
                'type' => 'recurring',
                'status' => 'cancelled',
                'name' => 'Yearly Plan',
                'price' => 299.99,
                'interval' => 'ANNUAL',
                'trial_days' => 30,
                'test' => true,
                'coupon_code' => 'WELCOME10',
                'cancelled_on' => now()->subDays(2),
                'cancellation_reason' => 'Customer requested cancellation',
                'created_at' => now()->subDays(10),
            ],
            // Free time grant for user 3
            [
                'user_id' => $createdUsers[2]->id,
                'charge_id' => null,
                'type' => 'free_time',
                'status' => 'active',
                'name' => 'Free Time Grant',
                'price' => 0,
                'interval' => null,
                'trial_days' => 0,
                'test' => true,
                'coupon_code' => null,
                'cancelled_on' => null,
                'free_until' => now()->addDays(15),
                'grant_reason' => 'Welcome bonus',
                'created_at' => now()->subDays(1),
            ],
            // Pending subscription (user accepted but not confirmed by webhook)
            [
                'user_id' => $createdUsers[0]->id,
                'charge_id' => 1003,
                'type' => 'recurring',
                'status' => 'pending',
                'name' => 'Lifetime Plan',
                'price' => 999.99,
                'interval' => 'ANNUAL',
                'trial_days' => 7,
                'test' => true,
                'coupon_code' => 'SAVE25',
                'cancelled_on' => null,
                'created_at' => now()->subHours(2),
            ],
        ];

        foreach ($testCharges as $chargeData) {
            // Add the foreign key field for shop/user relationship
            $foreignKey = \Osiset\ShopifyApp\Util::getShopsTableForeignKey();
            $chargeData[$foreignKey] = $chargeData['user_id'];
            
            $charge = Charge::firstOrCreate(
                [
                    'charge_id' => $chargeData['charge_id'],
                    $foreignKey => $chargeData['user_id']
                ],
                $chargeData
            );
            
            $this->command->info("Created/found charge: {$charge->name} for user {$charge->$foreignKey} (Status: {$charge->status})");
        }

        $this->command->info('Subscription test data seeding completed!');
        $this->command->info('');
        $this->command->info('Test users created:');
        foreach ($createdUsers as $user) {
            $this->command->info("- {$user->name} (ID: {$user->id}, Email: {$user->email})");
        }
        $this->command->info('');
        $this->command->info('You can now test the subscription endpoints using these user IDs.');
        $this->command->info('Visit /test/subscriptions for the test interface.');
    }
}