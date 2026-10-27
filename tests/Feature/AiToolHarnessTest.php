<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\Tag;
use App\Models\User;
use App\Services\Ai\Tools\DraftEntryTool;
use App\Services\Ai\Tools\GetUserStatsTool;
use App\Services\Ai\Tools\ListTagsTool;
use App\Services\Ai\Tools\SearchJournalTool;
use App\Services\Ai\Tools\ToolRegistry;
use App\Services\AiChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiToolHarnessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tool_registry_has_core_tools_and_valid_gemini_declarations(): void
    {
        $registry = new ToolRegistry();

        $this->assertTrue($registry->has('search_journal'));
        $this->assertTrue($registry->has('get_user_stats'));
        $this->assertTrue($registry->has('list_tags'));
        $this->assertTrue($registry->has('draft_entry'));

        $declarations = $registry->getGeminiDeclarations();
        $this->assertCount(4, $declarations);

        $names = array_column($declarations, 'name');
        $this->assertContains('search_journal', $names);
        $this->assertContains('get_user_stats', $names);
        $this->assertContains('list_tags', $names);
        $this->assertContains('draft_entry', $names);

        foreach ($declarations as $decl) {
            $this->assertArrayHasKey('name', $decl);
            $this->assertArrayHasKey('description', $decl);
            $this->assertArrayHasKey('parameters', $decl);
            $this->assertEquals('OBJECT', $decl['parameters']['type']);
        }
    }

    public function test_search_journal_tool_recalls_user_entry(): void
    {
        $user = User::factory()->create();
        Entry::create([
            'user_id' => $user->id,
            'title' => 'Sunday Forest Walk',
            'content' => 'Felt completely rejuvenated walking through the redwoods with cold morning air.',
        ]);

        $tool = new SearchJournalTool();
        $result = $tool->execute(['query' => 'redwoods'], $user);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(1, $result['count']);
        $this->assertEquals('Sunday Forest Walk', $result['entries'][0]['title']);
        $this->assertStringContainsString('redwoods', $result['entries'][0]['snippet']);
    }

    public function test_get_user_stats_tool_returns_streak_and_totals(): void
    {
        $user = User::factory()->create();
        Entry::create([
            'user_id' => $user->id,
            'title' => 'Day 1 Entry',
            'content' => 'First day writing consistently.',
        ]);

        $tool = new GetUserStatsTool();
        $result = $tool->execute([], $user);

        $this->assertArrayHasKey('current_streak_days', $result);
        $this->assertArrayHasKey('total_journal_entries', $result);
        $this->assertEquals(1, $result['total_journal_entries']);
    }

    public function test_draft_entry_tool_creates_entry_for_user(): void
    {
        $user = User::factory()->create();

        $tool = new DraftEntryTool();
        $result = $tool->execute([
            'title' => 'Setting Boundaries',
            'content' => 'Realized today that saying no preserves my energy.',
            'tags' => ['reflection', 'growth'],
        ], $user);

        $this->assertEquals('success', $result['status']);
        $this->assertDatabaseHas('entries', [
            'id' => $result['entry_id'],
            'user_id' => $user->id,
        ]);

        $entry = Entry::find($result['entry_id']);
        $this->assertEquals('Setting Boundaries', $entry->title);
        $this->assertEquals('Realized today that saying no preserves my energy.', $entry->content);
        $this->assertCount(2, $entry->tags);
    }

    public function test_draft_entry_tool_handles_hashtag_and_trimmed_tags(): void
    {
        $user = User::factory()->create();

        $tool = new DraftEntryTool();
        $result = $tool->execute([
            'title' => 'Evening Thoughts',
            'content' => 'Grateful for quiet moments.',
            'tags' => ['#gratitude', '#evening', ' peace '],
        ], $user);

        $this->assertEquals('success', $result['status']);
        $entry = Entry::find($result['entry_id']);
        $tagNames = $entry->tags->pluck('name')->all();

        $this->assertCount(3, $tagNames);
        $this->assertContains('gratitude', $tagNames);
        $this->assertContains('peace', $tagNames);
        $this->assertContains('evening', $tagNames);
    }

    public function test_list_tags_tool_returns_user_tags_and_counts(): void
    {
        $user = User::factory()->create();

        $entry1 = Entry::create([
            'user_id' => $user->id,
            'title' => 'Entry 1',
            'content' => 'First note',
        ]);
        $entry2 = Entry::create([
            'user_id' => $user->id,
            'title' => 'Entry 2',
            'content' => 'Second note',
        ]);

        $tag1 = Tag::create(['name' => 'ambition']);
        $tag2 = Tag::create(['name' => 'focus']);

        $entry1->tags()->sync([$tag1->id, $tag2->id]);
        $entry2->tags()->sync([$tag1->id]);

        $tool = new ListTagsTool();
        $result = $tool->execute([], $user);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(2, $result['total_unique_tags']);
        $this->assertCount(2, $result['tags']);
        $this->assertEquals('ambition', $result['tags'][0]['name']);
        $this->assertEquals(2, $result['tags'][0]['entries_count']);
        $this->assertEquals('focus', $result['tags'][1]['name']);
        $this->assertEquals(1, $result['tags'][1]['entries_count']);
    }
}
