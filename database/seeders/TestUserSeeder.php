<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user with Standard plan
        User::create([
            'name' => 'Test User',
            'email' => 'test@creditremedi.com',
            'password' => Hash::make('password123'),
            'contact_number' => '555-0123',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'CA',
            'zipcode' => '90210',
            'ssn_last4' => '1234',
            'plan_type' => 'starter',
            'paid_amount' => 49.00,
            'role' => 'regular',
            'email_verified_at' => now(),
        ]);

        echo "✅ Test user created successfully!\n";
        echo "📧 Email: test@creditremedi.com\n";
        echo "🔑 Password: password123\n";
        echo "📦 Plan: Standard (Starter)\n";
        echo "💰 Paid Amount: $49.00\n";
    }
}
