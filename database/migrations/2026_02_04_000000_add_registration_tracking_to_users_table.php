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
            $table->enum('registration_status', ['pending', 'completed', 'failed'])
                ->default('pending')
                ->after('plan_type');
            $table->text('registration_error')->nullable()->after('registration_status');
            $table->timestamp('payment_attempted_at')->nullable()->after('registration_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registration_status', 'registration_error', 'payment_attempted_at']);
        });
    }
};
