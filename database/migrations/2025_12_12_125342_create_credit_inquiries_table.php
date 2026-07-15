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
        Schema::create('credit_inquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('credit_report_id')->nullable();
            
            // Inquiry details
            $table->string('creditor_name');
            $table->string('inquiry_type')->nullable(); // Hard or Soft
            $table->date('inquiry_date')->nullable();
            $table->string('bureau')->nullable(); // TUC, EXP, EQF
            
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
        Schema::dropIfExists('credit_inquiries');
    }
};
