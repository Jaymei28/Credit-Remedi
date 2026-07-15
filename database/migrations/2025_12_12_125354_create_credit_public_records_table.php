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
        Schema::create('credit_public_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('credit_report_id')->nullable();
            
            // Public record details
            $table->string('record_type')->nullable(); // Bankruptcy, Tax Lien, Judgment, etc.
            $table->string('status')->nullable();
            $table->string('bureau')->nullable(); // TUC, EXP, EQF
            
            // Financial details
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('balance', 12, 2)->nullable();
            
            // Dates
            $table->date('filed_date')->nullable();
            $table->date('satisfied_date')->nullable();
            $table->date('closing_date')->nullable();
            
            // Court/Legal info
            $table->string('court_name')->nullable();
            $table->string('case_number')->nullable();
            $table->string('plaintiff')->nullable();
            $table->string('attorney')->nullable();
            
            // Additional info
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('credit_public_records');
    }
};
