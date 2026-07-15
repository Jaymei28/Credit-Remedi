<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('waitlist_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('challenge');
            $table->string('usage');
            $table->string('timeline');
            $table->unsignedBigInteger('referrer_id')->nullable();
            $table->integer('referral_count')->default(0);
            $table->boolean('is_qualified')->default(false);
            $table->string('referral_code')->unique()->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waitlist_users');
    }
};
