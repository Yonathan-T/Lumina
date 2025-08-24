<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Add indexes for better query performance
            $table->index(['user_id', 'last_activity', 'created_at'], 'conversations_user_activity_idx');
            $table->index(['user_id', 'created_at'], 'conversations_user_created_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            // Add indexes for better message loading performance
            $table->index(['conversation_id', 'created_at'], 'messages_conversation_created_idx');
            $table->index(['conversation_id', 'is_ai_response'], 'messages_conversation_ai_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_user_activity_idx');
            $table->dropIndex('conversations_user_created_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conversation_created_idx');
            $table->dropIndex('messages_conversation_ai_idx');
        });
    }
};
