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
        Schema::create('credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('credit_report_id')->nullable();
            
            // Account identification
            $table->string('creditor_name');
            $table->string('account_number')->nullable();
            $table->string('account_type')->nullable(); // Credit Card, Mortgage, Auto Loan, etc.
            $table->string('account_status')->nullable(); // Open, Closed, etc.
            
            // Bureau information
            $table->string('bureau')->nullable(); // TUC, EXP, EQF
            
            // Account details
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->decimal('current_balance', 12, 2)->nullable();
            $table->decimal('monthly_payment', 12, 2)->nullable();
            $table->decimal('original_amount', 12, 2)->nullable();
            $table->integer('term_months')->nullable();
            
            // Dates
            $table->date('date_opened')->nullable();
            $table->date('date_closed')->nullable();
            $table->date('last_payment_date')->nullable();
            $table->date('date_reported')->nullable();
            
            // Payment history
            $table->string('payment_status')->nullable(); // Current, Late, etc.
            $table->integer('months_reviewed')->nullable();
            $table->integer('times_30_days_late')->nullable();
            $table->integer('times_60_days_late')->nullable();
            $table->integer('times_90_days_late')->nullable();
            
            // Responsibility
            $table->string('responsibility')->nullable(); // Individual, Joint, etc.
            
            // Remarks/Comments
            $table->text('remarks')->nullable();
            $table->text('account_condition')->nullable();
            
            // Raw data for reference
            $table->json('raw_data')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('credit_report_id')->references('id')->on('credit_reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_accounts');
    }
};
