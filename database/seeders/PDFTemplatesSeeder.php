<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PDFTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'template_collection',
                'name' => 'Collection Account Strategy',
                'description' => 'Permissible Purpose + Data Breach Strategy',
                'content' => "Subject: Dispute of Collection Account – Lack of Permissible Purpose & Data Security Violation\n\nTo Whom It May Concern,\n\nI am disputing the collection account listed on my consumer report. I have not provided written authorization, nor have I entered into any agreement granting permissible purpose for this account to be accessed or reported.\n\nUnder 15 U.S.C. § 1681b, consumer reports may only be furnished for a permissible purpose. No such purpose exists in this matter. Additionally, due to ongoing data breaches within the financial industry, I am disputing the accuracy, security, and lawful handling of my personal information related to this account.\n\nFurther, pursuant to 15 U.S.C. § 1681c-2, I am requesting the immediate blocking and removal of any information resulting from unauthorized access or identity-related concerns. If this account is being reported without verified authorization, it must be deleted.\n\nUnder 15 U.S.C. § 1681e(b), you are required to maintain maximum possible accuracy. If you cannot fully verify this account with original contractual documentation, including consumer authorization, it must be deleted.\n\nPlease remove this account immediately and provide written confirmation of deletion.\n\nSincerely,\n[USER_NAME]",
                'category' => 'template',
            ],
            [
                'key' => 'template_chargeoff',
                'name' => 'Charge-Off Strategy',
                'description' => 'Improper Monthly Reporting Strategy',
                'content' => "Subject: Dispute of Charge-Off – Improper Monthly Reporting\n\nTo Whom It May Concern,\n\nI am disputing the charge-off account being reported monthly on my consumer report. A charge-off is a one-time accounting event and should not be reported as a recurring delinquency.\n\nUnder 15 U.S.C. § 1681s-2, furnishers are required to report information accurately. Monthly charge-off reporting misrepresents the status of the account and artificially suppresses my credit profile.\n\nAdditionally, if this debt has been canceled or written off, continued reporting may constitute inaccurate and misleading information under the Fair Credit Reporting Act.\n\nI am requesting immediate removal of this account or correction to reflect accurate, lawful reporting. If the debt has been canceled, please provide the corresponding 1099-C documentation.\n\nSincerely,\n[USER_NAME]",
                'category' => 'template',
            ],
            [
                'key' => 'template_late_payment',
                'name' => 'Late Payment Strategy',
                'description' => 'Computation Error / Fee Reversal Strategy',
                'content' => "Subject: Dispute of Late Payment – Computation Error\n\nTo Whom It May Concern,\n\nI am disputing the late payment reported on the above-referenced account. The creditor has reversed the late fee associated with this alleged delinquency, confirming a billing and computation error.\n\nUnder 15 U.S.C. § 1666b, creditors are required to accurately reflect billing statements. The reversal of the late fee demonstrates the delinquency was reported in error.\n\nFurther, under 15 U.S.C. § 1681e(b), consumer reporting agencies must maintain maximum possible accuracy. Since the late fee was reversed, the continued reporting of a late payment is inaccurate and must be removed.\n\nPlease update or delete this late payment immediately.\n\nSincerely,\n[USER_NAME]",
                'category' => 'template',
            ],
        ];

        foreach ($templates as $template) {
            DB::table('bot_prompts')->updateOrInsert(
                ['key' => $template['key']],
                array_merge($template, [
                    'updated_at' => Carbon::now(),
                ])
            );
        }
    }
}
