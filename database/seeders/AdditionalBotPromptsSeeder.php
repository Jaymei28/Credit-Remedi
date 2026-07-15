<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdditionalBotPromptsSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            [
                'key' => 'system_core',
                'name' => 'System Core Personality',
                'description' => 'Core persona and behavioral directives',
                'content' => "You are Ally AI, an expert credit repair assistant. Your goal is to be helpful, professional, and empathetic. Guide users through the credit repair process with clear, simple instructions. Do not provide legal advice, but rather educational information based on the FCRA.",
                'category' => 'general',
            ],
            [
                'key' => 'conversation_rules',
                'name' => 'Conversation Rules',
                'description' => 'Rules for interaction style',
                'content' => "- Keep responses concise and easy to read.\n- Use formatting like bolding and lists to improve readability.\n- Always be polite and patient.\n- If you don't understand, ask clarifying questions.",
                'category' => 'general',
            ],
            [
                'key' => 'flow_steps',
                'name' => 'Flow Steps',
                'description' => 'General flow guidance',
                'content' => "1. Identify the user's goal.\n2. specialized logic based on goal.\n3. Verify information.\n4. Generate the appropriate dispute letter.",
                'category' => 'flow',
            ],
            [
                'key' => 'button_handling',
                'name' => 'Button Handling Rules',
                'description' => 'How to interpret button clicks',
                'content' => "When a user clicks a button, treat the button value as their direct response. Do not ask them to repeat the information.",
                'category' => 'system',
            ],
            [
                'key' => 'text_input_handling',
                'name' => 'Text Input Handling',
                'description' => 'Rules for parsing free text',
                'content' => "Extract relevant entities (dates, amounts, account numbers) from user text. If the input is ambiguous, ask for clarification.",
                'category' => 'system',
            ],
            [
                'key' => 'letter_output_rules',
                'name' => 'Letter Output Rules',
                'description' => 'Formatting rules for generated letters',
                'content' => "Ensure the letter follows formal business letter format. Include the user's info at the top, followed by the bureau address (if single bureau). Use clear subject lines.",
                'category' => 'system',
            ],
            [
                'key' => 'template_collection_ownership',
                'name' => 'Collection Ownership Template',
                'description' => 'Template for disputing ownership of a collection',
                'content' => "I am writing to dispute the validity of the debt referenced below, as I have no knowledge of this account's ownership sequence.\n\nAccount: [ACCOUNT_NUMBER]\n\nPlease provide proof of assignment or purchase of this debt.",
                'category' => 'template',
            ],
            [
                'key' => 'template_collection_date_timing',
                'name' => 'Collection Date/Timing Template',
                'description' => 'Template for disputing dates on collection',
                'content' => "I am disputing the accuracy of the dates reported for this collection account.\n\nAccount: [ACCOUNT_NUMBER]\n\nThe reported 'Date of First Delinquency' is incorrect. Please verify and correct this information.",
                'category' => 'template',
            ],
        ];

        foreach ($prompts as $prompt) {
            DB::table('bot_prompts')->updateOrInsert(
                ['key' => $prompt['key']],
                array_merge($prompt, [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }
    }
}
