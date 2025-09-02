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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('external_url')->unique();
            $table->string('source_name');
            $table->string('source_url');
            $table->timestamp('published_at');
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamp('cached_at');
            $table->timestamps();

            $table->index(['published_at', 'source_name']);
            $table->index('category');
            $table->index('cached_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
