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
        Schema::table('credit_reports', function (Blueprint $table) {
            $table->integer('total_accounts_count')->nullable();
            $table->integer('open_accounts_count')->nullable();
            $table->integer('negative_accounts_count')->nullable();
            $table->integer('hard_inquiries_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_reports', function (Blueprint $table) {
            $table->dropColumn(['total_accounts_count', 'open_accounts_count', 'negative_accounts_count', 'hard_inquiries_count']);
        });
    }
};
