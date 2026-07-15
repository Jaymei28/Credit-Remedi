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
        Schema::create('credit_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('credit_report_id')->nullable();
            
            // Score details
            $table->string('bureau'); // TUC, EXP, EQF
            $table->integer('score');
            $table->string('score_model')->nullable(); // VantageScore, FICO, etc.
            $table->string('lender_rank')->nullable(); // Excellent, Good, Fair, Poor
            $table->string('score_scale')->default('300-850');
            
            // Risk factors
            $table->json('risk_factors')->nullable();
            
            // Report date
            $table->date('report_date')->nullable();
            
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
        Schema::dropIfExists('credit_scores');
    }
};
