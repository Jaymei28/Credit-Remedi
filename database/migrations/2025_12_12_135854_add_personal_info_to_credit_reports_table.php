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
            $table->json('personal_info')->nullable()->after('extracted_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_reports', function (Blueprint $table) {
            $table->dropColumn('personal_info');
        });
    }
};
