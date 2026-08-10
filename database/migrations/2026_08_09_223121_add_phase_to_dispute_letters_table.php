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
        Schema::table('dispute_letters', function (Blueprint $table) {
            $table->integer('phase')->default(1)->after('desired_resolution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispute_letters', function (Blueprint $table) {
            $table->dropColumn('phase');
        });
    }
};
