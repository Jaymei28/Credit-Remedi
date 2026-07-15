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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('identityiq_enrolled')->default(false)->after('plan_type');
            $table->timestamp('identityiq_enrolled_at')->nullable()->after('identityiq_enrolled');
            $table->boolean('initial_report_uploaded')->default(false)->after('identityiq_enrolled_at');
            $table->timestamp('initial_report_uploaded_at')->nullable()->after('initial_report_uploaded');
            $table->boolean('onboarding_completed')->default(false)->after('initial_report_uploaded_at');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'identityiq_enrolled',
                'identityiq_enrolled_at',
                'initial_report_uploaded',
                'initial_report_uploaded_at',
                'onboarding_completed',
                'onboarding_completed_at'
            ]);
        });
    }
};
