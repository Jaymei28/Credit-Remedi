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
        Schema::create('fundability_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('score')->default(0); // 0-100 score
            $table->string('grade')->nullable(); // A, B, C, D, F
            $table->json('factors')->nullable(); // Factors affecting the score
            $table->json('recommendations')->nullable(); // Improvement recommendations
            $table->json('strengths')->nullable(); // Positive factors
            $table->json('weaknesses')->nullable(); // Negative factors
            $table->integer('credit_score')->nullable(); // Average credit score
            $table->decimal('debt_to_income_ratio', 5, 2)->nullable();
            $table->integer('total_accounts')->default(0);
            $table->integer('open_accounts')->default(0);
            $table->integer('hard_inquiries')->default(0);
            $table->integer('negative_items')->default(0);
            $table->timestamps();
        });

        Schema::create('lenders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // bank, credit_union, online, alternative
            $table->text('description')->nullable();
            $table->integer('min_credit_score')->default(0);
            $table->integer('max_credit_score')->default(850);
            $table->decimal('min_amount', 12, 2)->default(0);
            $table->decimal('max_amount', 12, 2)->default(0);
            $table->decimal('min_apr', 5, 2)->nullable();
            $table->decimal('max_apr', 5, 2)->nullable();
            $table->string('application_url')->nullable();
            $table->json('requirements')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('lender_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lender_id')->constrained()->onDelete('cascade');
            $table->foreignId('fundability_score_id')->constrained()->onDelete('cascade');
            $table->integer('match_score')->default(0); // 0-100
            $table->string('approval_likelihood'); // high, medium, low
            $table->decimal('estimated_apr_min', 5, 2)->nullable();
            $table->decimal('estimated_apr_max', 5, 2)->nullable();
            $table->json('match_reasons')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lender_matches');
        Schema::dropIfExists('lenders');
        Schema::dropIfExists('fundability_scores');
    }
};
