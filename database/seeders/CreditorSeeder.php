<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Creditor;

class CreditorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creditors = [
            // Collection Agencies
            ['name' => 'Midland Credit Management', 'type' => 'collector'],
            ['name' => 'Portfolio Recovery Associates', 'type' => 'collector'],
            ['name' => 'LVNV Funding LLC', 'type' => 'collector'],
            ['name' => 'Jefferson Capital Systems', 'type' => 'collector'],
            ['name' => 'Cavalry SPV I, LLC', 'type' => 'collector'],
            ['name' => 'Enhanced Recovery Company', 'type' => 'collector'],
            ['name' => 'IC System', 'type' => 'collector'],
            ['name' => 'Resurgent Capital Services', 'type' => 'collector'],
            
            // Major Creditors
            ['name' => 'Capital One', 'type' => 'creditor'],
            ['name' => 'Chase Bank', 'type' => 'creditor'],
            ['name' => 'Citibank', 'type' => 'creditor'],
            ['name' => 'Synchrony Bank', 'type' => 'creditor'],
            ['name' => 'Comenity Bank', 'type' => 'creditor'],
            ['name' => 'Bank of America', 'type' => 'creditor'],
            ['name' => 'American Express', 'type' => 'creditor'],
            ['name' => 'Discover', 'type' => 'creditor'],
            ['name' => 'Wells Fargo', 'type' => 'creditor'],
            ['name' => 'Navy Federal Credit Union', 'type' => 'creditor'],
            ['name' => 'Barclays Bank Delaware', 'type' => 'creditor'],
            ['name' => 'Credit One Bank', 'type' => 'creditor'],
        ];

        foreach ($creditors as $creditor) {
            Creditor::create($creditor);
        }
    }
}
