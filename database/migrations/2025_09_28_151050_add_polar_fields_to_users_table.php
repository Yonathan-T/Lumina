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
        // 1. Add the CRITICAL 'is_subscribed' flag to the users table
        // We see other subscription columns exist, but this simple flag is needed for quick access checks.
        Schema::table('users', function (Blueprint $table) {
            // Flag to check if the user is currently subscribed
            $table->boolean('is_subscribed')->default(false)->after('polar_customer_id');
        });

        // Note: Email field is now included in the main polar_customers migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_subscribed')) {
                $table->dropColumn('is_subscribed');
            }
        });

        if (Schema::hasTable('polar_customers')) {
            Schema::table('polar_customers', function (Blueprint $table) {
                if (Schema::hasColumn('polar_customers', 'email')) {
                    $table->dropColumn('email');
                }
            });
        }
    }
};
