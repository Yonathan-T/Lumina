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
        Schema::table('conversations', function (Blueprint $table) {
            $table->text('summary')->nullable()->after('type');
            $table->foreignId('last_summarized_message_id')->nullable()->after('summary')->constrained('messages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['last_summarized_message_id']);
            $table->dropColumn(['summary', 'last_summarized_message_id']);
        });
    }
};
