<?php

namespace App\Services\Ai\Tools;

use App\Models\User;

class ToolRegistry
{
    /**
     * @var array<string, ToolInterface>
     */
    protected array $tools = [];

    public function __construct()
    {
        $this->register(new SearchJournalTool());
        $this->register(new GetUserStatsTool());
        $this->register(new ListTagsTool());
        $this->register(new DraftEntryTool());
    }

    public function register(ToolInterface $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    /**
     * Returns function declarations formatted for Google Gemini API
     */
    public function getGeminiDeclarations(): array
    {
        $declarations = [];

        foreach ($this->tools as $tool) {
            $declarations[] = $tool->definition();
        }

        return $declarations;
    }

    /**
     * Execute a named tool with arguments for a given user
     */
    public function execute(string $name, array $args, User $user): array
    {
        if (! isset($this->tools[$name])) {
            return [
                'status' => 'error',
                'message' => "Tool '{$name}' is not recognized.",
            ];
        }

        return $this->tools[$name]->execute($args, $user);
    }

    public function has(string $name): bool
    {
        return isset($this->tools[$name]);
    }
}
