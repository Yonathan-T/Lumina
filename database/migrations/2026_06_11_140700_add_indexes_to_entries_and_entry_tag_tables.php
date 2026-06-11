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
        try {
            Schema::table('entries', function (Blueprint $table) {
                $table->index('user_id');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('entry_tag', function (Blueprint $table) {
                $table->index(['entry_id', 'tag_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('entry_tag', function (Blueprint $table) {
                $table->index(['tag_id', 'entry_id']);
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('entries', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('entry_tag', function (Blueprint $table) {
                $table->dropIndex(['entry_id', 'tag_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('entry_tag', function (Blueprint $table) {
                $table->dropIndex(['tag_id', 'entry_id']);
            });
        } catch (\Exception $e) {}
    }
};
