<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DisputeLetter;
use Illuminate\Support\Facades\Hash;

class DemoClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🚀 Creating demo client accounts...\n\n";

        // Clean up existing demo accounts if they exist
        User::whereIn('email', ['john.standard@demo.com', 'sarah.pro@demo.com'])->delete();
        echo "🧹 Cleaned up existing demo accounts\n\n";

        // ============================================
        // CLIENT 1: STANDARD PLAN
        // ============================================
        $standardClient = User::create([
            'name' => 'John Smith',
            'email' => 'john.standard@demo.com',
            'password' => Hash::make('password123'),
            'contact_number' => '555-1001',
            'address' => '456 Oak Avenue',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zipcode' => '90001',
            'ssn_last4' => '5678',
            'plan_type' => 'starter',
            'paid_amount' => 49.00,
            'role' => 'regular',
            'email_verified_at' => now(),
        ]);

        echo "✅ Created Standard Plan Client: {$standardClient->name}\n";
        echo "   📧 Email: {$standardClient->email}\n";
        echo "   🔑 Password: password123\n";
        echo "   📦 Plan: Standard (Starter) - \$49.00\n\n";

        // Add sample disputes for Standard client
        $this->createSampleDisputes($standardClient, 3);

        // ============================================
        // CLIENT 2: PRO PLAN (PREMIUM)
        // ============================================
        $proClient = User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah.pro@demo.com',
            'password' => Hash::make('password123'),
            'contact_number' => '555-2002',
            'address' => '789 Maple Street',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zipcode' => '94102',
            'ssn_last4' => '9012',
            'plan_type' => 'premium',
            'paid_amount' => 69.00,
            'role' => 'regular',
            'email_verified_at' => now(),
        ]);

        echo "✅ Created Pro Plan Client: {$proClient->name}\n";
        echo "   📧 Email: {$proClient->email}\n";
        echo "   🔑 Password: password123\n";
        echo "   📦 Plan: Pro (Premium) - \$69.00\n\n";

        // Add more sample disputes for Pro client
        $this->createSampleDisputes($proClient, 5);

        echo "\n🎉 Demo clients created successfully!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📋 SUMMARY:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Standard Plan Client:\n";
        echo "  Email: john.standard@demo.com\n";
        echo "  Password: password123\n";
        echo "  Disputes: 3\n\n";
        echo "Pro Plan Client:\n";
        echo "  Email: sarah.pro@demo.com\n";
        echo "  Password: password123\n";
        echo "  Disputes: 5\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }

    /**
     * Create sample dispute letters for a user
     */
    private function createSampleDisputes(User $user, int $count): void
    {
        $creditors = [
            'Experian',
            'Equifax',
            'TransUnion',
            'Capital One',
            'Chase Bank',
            'Bank of America',
            'Wells Fargo',
            'Discover',
            'American Express',
            'Citibank'
        ];

        $disputeReasons = [
            'Account does not belong to me',
            'Incorrect payment status',
            'Account already paid',
            'Duplicate account',
            'Incorrect balance amount',
            'Account is older than 7 years',
            'Never late on payments',
            'Identity theft'
        ];

        for ($i = 0; $i < $count; $i++) {
            $creditor = $creditors[array_rand($creditors)];
            $reason = $disputeReasons[array_rand($disputeReasons)];
            $accountNumber = 'XXXX-XXXX-' . rand(1000, 9999);
            $isPosted = $i % 2 === 0; // Alternate between posted and pending

            $letterContent = $this->generateDisputeLetter($user, $creditor, $reason, $accountNumber);

            DisputeLetter::create([
                'user_id' => $user->id,
                'creditor_name' => $creditor,
                'account_number' => $accountNumber,
                'dispute_reason' => $reason,
                'letter_content' => $letterContent,
                'posted_1' => $isPosted,
                'sent' => $isPosted,
                'sent_date' => $isPosted ? now()->subDays(rand(1, 15)) : null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 10)),
            ]);
        }

        echo "   📄 Created {$count} sample disputes\n";
    }

    /**
     * Generate a realistic dispute letter
     */
    private function generateDisputeLetter(User $user, string $creditor, string $reason, string $accountNumber): string
    {
        $date = now()->format('F d, Y');
        
        return "{$user->name}
{$user->address}
{$user->city}, {$user->state} {$user->zipcode}

{$date}

{$creditor}
Credit Reporting Department
P.O. Box 1234
City, State 12345

Subject: Formal Dispute of Inaccurate Information on Credit Report

Dear Sir/Madam,

I am writing to formally dispute inaccurate information appearing on my credit report. I have recently reviewed my credit report and discovered the following error that requires immediate correction:

Account Number: {$accountNumber}
Creditor: {$creditor}
Dispute Reason: {$reason}

This information is inaccurate and is negatively affecting my credit score. Under the Fair Credit Reporting Act (FCRA), I have the right to dispute incomplete or inaccurate information.

I request that you:
1. Conduct a thorough investigation of this matter
2. Verify the accuracy of this information with the original creditor
3. Remove or correct this information if it cannot be verified
4. Provide me with written confirmation of the results

Please investigate this matter within 30 days as required by law and notify me of the results in writing.

I have enclosed copies of supporting documentation. Please contact me if you need any additional information.

Thank you for your prompt attention to this matter.

Sincerely,

{$user->name}
Phone: {$user->contact_number}
SSN (Last 4): {$user->ssn_last4}";
    }
}
