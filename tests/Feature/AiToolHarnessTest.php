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

    public function test_search_journal_prunes_weak_pure_semantic_noise_below_dual_tier_threshold(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'title' => 'Testing security',
            'content' => 'this is a test to check if the data is encrypted in the DB',
        ]);

        $searchService = $this->createMock(\App\Services\EntrySearchService::class);
        $searchService->method('findMatchingEntryIds')->willReturn(collect());

        $embeddingService = $this->createMock(\App\Services\EmbeddingService::class);
        $embeddingService->method('findSemanticMatches')->willReturn(collect([
            ['entry_id' => $entry->id, 'score' => 0.58], // ambient noise below 0.60
        ]));

        $tool = new SearchJournalTool($searchService, $embeddingService);
        $result = $tool->execute(['query' => 'fraol selam'], $user);

        $this->assertEquals('not_found', $result['status']);
        $this->assertEquals(0, $result['count']);
    }

    public function test_search_journal_accepts_pure_semantic_matches_meeting_dual_tier_threshold(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'title' => 'Deep Work Routines',
            'content' => 'Spending early mornings without distraction brings high focus and clarity.',
        ]);

        $searchService = $this->createMock(\App\Services\EntrySearchService::class);
        $searchService->method('findMatchingEntryIds')->willReturn(collect());

        $embeddingService = $this->createMock(\App\Services\EmbeddingService::class);
        $embeddingService->method('findSemanticMatches')->willReturn(collect([
            ['entry_id' => $entry->id, 'score' => 0.68],
        ]));

        $tool = new SearchJournalTool($searchService, $embeddingService);
        $result = $tool->execute(['query' => 'productivity habits'], $user);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(1, $result['count']);
        $this->assertEquals('conceptual_similarity', $result['entries'][0]['match_type']);
        $this->assertEquals('Deep Work Routines', $result['entries'][0]['title']);
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

    public function test_search_journal_handles_earliest_order_by(): void
    {
        $user = User::factory()->create();

        $oldest = Entry::create([
            'user_id' => $user->id,
            'title' => 'Ancient Beginning',
            'content' => 'The very first start.',
            'created_at' => now()->subMonths(6),
        ]);

        $newer = Entry::create([
            'user_id' => $user->id,
            'title' => 'Recent Reflection',
            'content' => 'Much later thought.',
            'created_at' => now()->subDay(),
        ]);

        $tool = new SearchJournalTool();
        $result = $tool->execute(['order_by' => 'earliest'], $user);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Ancient Beginning', $result['entries'][0]['title']);
        $this->assertEquals('earliest_entry', $result['entries'][0]['match_type']);
    }

    public function test_search_journal_auto_detects_first_entry_natural_query(): void
    {
        $user = User::factory()->create();

        $oldest = Entry::create([
            'user_id' => $user->id,
            'title' => 'First Day at Clinic',
            'content' => 'Felt nervous but eager.',
            'created_at' => now()->subMonths(3),
        ]);

        $newer = Entry::create([
            'user_id' => $user->id,
            'title' => 'Recent Routine',
            'content' => 'Settled in now.',
            'created_at' => now()->subDay(),
        ]);

        $tool = new SearchJournalTool();
        $result = $tool->execute(['query' => 'what was my first entry?'], $user);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('First Day at Clinic', $result['entries'][0]['title']);
        $this->assertEquals('earliest_entry', $result['entries'][0]['match_type']);
    }

    public function test_search_journal_handles_longest_order_by(): void
    {
        $user = User::factory()->create();

        $short = Entry::create([
            'user_id' => $user->id,
            'title' => 'Short Note',
            'content' => 'Just two words.',
        ]);

        $longContent = implode(' ', array_fill(0, 150, 'reflection'));
        $long = Entry::create([
            'user_id' => $user->id,
            'title' => 'Comprehensive Reflection',
            'content' => $longContent,
        ]);

        $tool = new SearchJournalTool();
        $result = $tool->execute(['order_by' => 'longest'], $user);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Comprehensive Reflection', $result['entries'][0]['title']);
        $this->assertEquals('longest_entry', $result['entries'][0]['match_type']);
        $this->assertGreaterThanOrEqual(150, $result['entries'][0]['word_count']);
    }

    public function test_get_user_stats_includes_longest_and_first_entry_metrics(): void
    {
        $user = User::factory()->create();

        Entry::create([
            'user_id' => $user->id,
            'title' => 'Early Days',
            'content' => 'First reflection.',
            'created_at' => now()->subDays(10),
        ]);

        Entry::create([
            'user_id' => $user->id,
            'title' => 'Detailed Essay',
            'content' => 'This is a much longer and more detailed entry with many words to test counting.',
            'created_at' => now()->subDays(2),
        ]);

        $tool = new GetUserStatsTool();
        $result = $tool->execute([], $user);

        $this->assertEquals('Early Days', $result['first_entry_title']);
        $this->assertEquals('Detailed Essay', $result['longest_entry_title']);
        $this->assertGreaterThan(5, $result['longest_entry_word_count']);
    }

    public function test_chat_interface_renders_pills_without_placeholder_collision(): void
    {
        $ci = new \App\Livewire\ChatInterface();

        $message = [
            'isAi' => true,
            'content' => "Your very first reflection in the journal goes back to October 9, 2025, titled Entry 1.\n\nIn that entry, you reflected on ambition.",
            'metadata' => [
                'tools_used' => [
                    [
                        'tool' => 'search_journal',
                        'result' => [
                            'entries' => [
                                [
                                    'id' => 1,
                                    'title' => 'Entry 1',
                                    'content' => 'Content 1',
                                ],
                                [
                                    'id' => 5,
                                    'title' => 'Entry 5',
                                    'content' => 'Content 5',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $rendered = $ci->renderMessageContent($message);

        // Must not contain any mangled placeholder fragments
        $this->assertStringNotContainsString('PILL_', $rendered);
        $this->assertStringNotContainsString('___', $rendered);

        // Must contain valid entry preview button
        $this->assertStringContainsString('data-entry-preview="true"', $rendered);
        $this->assertStringContainsString('Entry 1', $rendered);
        $this->assertStringContainsString('In that entry, you reflected on ambition.', $rendered);
    }

    public function test_search_journal_handles_compound_first_and_longest_query(): void
    {
        $user = User::factory()->create();

        $earliest = Entry::create([
            'user_id' => $user->id,
            'title' => 'First Childhood Memory',
            'content' => 'Short reflection on childhood.',
            'created_at' => now()->subYears(2),
        ]);

        $longestContent = implode(' ', array_fill(0, 200, 'in-depth analysis'));
        $longest = Entry::create([
            'user_id' => $user->id,
            'title' => 'Deep Philosophy of Mind',
            'content' => $longestContent,
            'created_at' => now()->subMonths(6),
        ]);

        $tool = new SearchJournalTool();
        $result = $tool->execute([
            'query' => 'you rememebr my first entry? what was it about? also what is the longest entry i have?'
        ], $user);

        $this->assertEquals('success', $result['status']);
        $this->assertCount(2, $result['entries']);
        $this->assertEquals('First Childhood Memory', $result['entries'][0]['title']);
        $this->assertEquals('earliest_entry', $result['entries'][0]['match_type']);
        $this->assertEquals('Deep Philosophy of Mind', $result['entries'][1]['title']);
        $this->assertEquals('longest_entry', $result['entries'][1]['match_type']);
        $this->assertEquals('Deep Philosophy of Mind', $result['milestones']['longest_entry_title']);
        $this->assertEquals('First Childhood Memory', $result['milestones']['first_entry_title']);
    }

    public function test_render_message_content_parses_markdown_headings(): void
    {
        $ci = new \App\Livewire\ChatInterface();

        $message = [
            'isAi' => true,
            'content' => "Here is how to think about chewing:\n\n### Is Chewing Physical or Chemical?\nChewing is actually a physical change!\n\n### 3 More Examples of Chemical Changes\n1. A glowing glowstick\n(Extra fact!), —\n\n## Summary Note",
            'metadata' => [],
        ];

        $rendered = $ci->renderMessageContent($message);

        // Raw hashes should not be visible in rendered output
        $this->assertStringNotContainsString('###', $rendered);
        $this->assertStringNotContainsString('##', $rendered);

        // Should contain styled heading spans
        $this->assertStringContainsString('Is Chewing Physical or Chemical?</span>', $rendered);
        $this->assertStringContainsString('3 More Examples of Chemical Changes</span>', $rendered);
        $this->assertStringContainsString('Summary Note</span>', $rendered);
        $this->assertStringContainsString('font-semibold text-white', $rendered);
        $this->assertStringContainsString('font-bold text-white', $rendered);

        // Dashes preceded by punctuation should not leave stray commas
        $this->assertStringNotContainsString('!), -', $rendered);
        $this->assertStringNotContainsString('!), ,', $rendered);
    }
}
