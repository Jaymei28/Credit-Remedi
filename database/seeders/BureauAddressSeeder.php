<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BureauAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bureaus = [
            [
                'name' => 'Equifax',
                'address_line_1' => 'Post Office Box 105314',
                'address_line_2' => null,
                'city' => 'Atlanta',
                'state' => 'GA',
                'zip' => '30348',
                'full_address' => "Equifax Information Services\nCredit Reporting Agency\nATTN: Disputes Department\nPost Office Box 105314\nAtlanta, GA 30348",
                'active' => true,
            ],
            [
                'name' => 'Experian',
                'address_line_1' => 'Post Office Box 9701',
                'address_line_2' => null,
                'city' => 'Allen',
                'state' => 'TX',
                'zip' => '75013',
                'full_address' => "Experian Credit Services\nCredit Reporting Agency\nATTN: Disputes Department\nPost Office Box 9701\nAllen, TX 75013",
                'active' => true,
            ],
            [
                'name' => 'TransUnion',
                'address_line_1' => 'Post Office Box 2000',
                'address_line_2' => null,
                'city' => 'Chester',
                'state' => 'PA',
                'zip' => '19016',
                'full_address' => "Transunion Credit Services\nCredit Reporting Agency\nATTN: Disputes Department\nPost Office Box 2000\nChester, PA 19016",
                'active' => true,
            ],
        ];

        foreach ($bureaus as $bureau) {
            DB::table('bureau_addresses')->updateOrInsert(
                ['name' => $bureau['name']],
                $bureau
            );
        }
    }
}
