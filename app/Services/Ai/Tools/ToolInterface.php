<?php

namespace App\Services\Ai\Tools;

use App\Models\User;

interface ToolInterface
{
    /**
     * The unique machine name of the tool (e.g. search_journal)
     */
    public function name(): string;

    /**
     * The Gemini/OpenAI compatible function declaration schema
     */
    public function definition(): array;

    /**
     * Execute the tool with provided arguments for the authenticated user
     */
    public function execute(array $args, User $user): array;
}
