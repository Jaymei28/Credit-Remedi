<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lenders', function (Blueprint $table) {
            $table->string('bureau_pull')->nullable()->after('type'); // Primary, Experian, TransUnion, etc.
            $table->string('recommended_score')->nullable()->after('max_credit_score'); // 680-700+, 660+, etc.
            $table->string('score_model')->nullable()->after('recommended_score'); // FICO (primary), etc.
            $table->integer('intro_apr_months')->nullable()->after('max_apr'); // 0% APR period (12, 15, 18, 21 months)
            $table->string('income_sensitivity')->nullable()->after('intro_apr_months'); // Low, Medium, High
            $table->string('inquiry_sensitivity')->nullable()->after('income_sensitivity'); // Low, Medium, High
            $table->text('notes')->nullable()->after('requirements'); // Additional notes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lenders', function (Blueprint $table) {
            $table->dropColumn([
                'bureau_pull',
                'recommended_score',
                'score_model',
                'intro_apr_months',
                'income_sensitivity',
                'inquiry_sensitivity',
                'notes'
            ]);
        });
    }
};
