<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CreditBuildersController extends Controller
{
    public function index()
    {
        $creditBuilders = [
            [
                'name' => 'Rental Kharma',
                'title' => 'Build Your Credit With Rent',
                'description' => 'Report your rent payments to build credit history',
                'url' => 'https://www.rentalkharma.com',
                'promo_code' => 'RMILLS25',
                'promo_description' => 'Save $25 during sign up',
                'logo' => 'images/rentalkharma.png',
            ],
            [
                'name' => 'RentReporters',
                'title' => 'Report Rent Payments, Build Your Credit Score',
                'description' => 'Get credit for paying rent on time',
                'url' => 'https://www.rentreporters.com',
                'promo_code' => 'APP25',
                'promo_description' => 'Receive $25 off when you sign up through app',
                'logo' => 'images/RentReporters.png',
            ],
            [
                'name' => 'Experian Boost',
                'title' => 'Check Your Free Credit Report & FICO® Score',
                'description' => 'Create an Experian account. Make sure your bank account has your name on it so you can link utility & rental payments.',
                'url' => 'https://www.experian.com/consumer-products/credit-report.html',
                'promo_code' => null,
                'promo_description' => null,
                'logo' => 'images/Experian Boost.png',
            ],
            [
                'name' => 'eCredable',
                'title' => 'Report Your Utility Bills to the Credit Bureaus',
                'description' => 'You can use this for personal and business credit. If you\'re buying a home, only link accounts that don\'t fluctuate so your DTI isn\'t affected.',
                'url' => 'https://www.ecredable.com',
                'promo_code' => null,
                'promo_description' => null,
                'logo' => 'images/eCredable.png',
            ],
            [
                'name' => 'BoomPay',
                'title' => 'Rent Reporting to Build Credit',
                'description' => 'Turn your rent into credit building power',
                'url' => 'https://www.boom.money',
                'promo_code' => null,
                'promo_description' => null,
                'logo' => 'images/BoomPay.png',
            ],
            [
                'name' => 'CreditRentBoost',
                'title' => 'Rent Reporting To Boost Your Credit Score',
                'description' => 'Get credit for your rent payments',
                'url' => 'https://www.creditrentboost.com',
                'promo_code' => null,
                'promo_description' => null,
                'logo' => 'images/CreditRentBoost.png',
            ],
        ];

        return view('credit-builders.index', compact('creditBuilders'));
    }
}
