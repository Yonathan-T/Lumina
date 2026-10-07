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
            $table->text('elevenlabs_api_key')->nullable()->after('api_key');
            $table->timestamp('elevenlabs_api_key_verified_at')->nullable()->after('api_key_tested_at');
            $table->timestamp('elevenlabs_api_key_tested_at')->nullable()->after('elevenlabs_api_key_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'elevenlabs_api_key',
                'elevenlabs_api_key_verified_at',
                'elevenlabs_api_key_tested_at',
            ]);
        });
    }
};
