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
        Schema::dropIfExists('entry_search_terms');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('entry_search_terms')) {
            Schema::create('entry_search_terms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entry_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('term', 64);

                $table->unique(['entry_id', 'term']);
                $table->index(['user_id', 'term']);
            });
        }
    }
};
