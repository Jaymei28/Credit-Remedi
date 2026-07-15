<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('dispute_letters', function (Blueprint $table) {
            $table->id();
            $table->string('credit_bureau')->nullable();
            $table->string('credit_item_type')->nullable();
            $table->string('creditor_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('dispute_reason')->nullable();
            $table->string('desired_resolution')->nullable();
            $table->longText('letter_content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dispute_letters');
    }
};
