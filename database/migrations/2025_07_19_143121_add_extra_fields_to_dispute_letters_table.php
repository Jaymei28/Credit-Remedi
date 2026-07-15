<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dispute_letters', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->text('letter_content_2')->nullable()->after('letter_content');
            $table->timestamp('letter_content_2_ts')->nullable()->after('letter_content_2');
            $table->boolean('posted_1')->default(false)->after('letter_content_2_ts');
            $table->timestamp('posted_1_ts')->nullable()->after('posted_1');
            $table->boolean('posted_2')->default(false)->after('posted_1_ts');
            $table->timestamp('posted_2_ts')->nullable()->after('posted_2');
        });
    }

    public function down(): void
    {
        Schema::table('dispute_letters', function (Blueprint $table) {
            $table->dropColumn([
                'user_id',
                'letter_content_2',
                'letter_content_2_ts',
                'posted_1',
                'posted_1_ts',
                'posted_2',
                'posted_2_ts',
            ]);
        });
    }
};
