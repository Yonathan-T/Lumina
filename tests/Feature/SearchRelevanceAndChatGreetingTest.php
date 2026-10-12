<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\Tag;
use App\Models\User;
use App\Models\Conversation;
use App\Services\EntrySearchService;
use App\Livewire\ChatInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchRelevanceAndChatGreetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_relevance_orders_title_before_content_before_tags()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Tag-only match (title and content don't have 'test')
        $tagOnlyEntry = Entry::create([
            'user_id' => $user->id,
            'title' => 'Red Bull',
            'content' => 'Red Bull is taking so long',
            'created_at' => now(),
        ]);
        $testTag = Tag::create(['name' => 'test']);
        $tagOnlyEntry->tags()->attach($testTag->id);

        // 2. Title partial match
        $titlePartialEntry = Entry::create([
            'user_id' => $user->id,
            'title' => 'Testing security',
            'content' => 'this is a test to check if the data is encrypted in the DB',
            'created_at' => now()->subDay(),
        ]);

        // 3. Exact title match
        $titleExactEntry = Entry::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'content' => 'testing banners',
            'created_at' => now()->subDays(2),
        ]);

        $searchService = app(EntrySearchService::class);
        $matchedIds = $searchService->findMatchingEntryIds($user->id, 'test', 10)->values()->all();

        // Exact title and title partial matches MUST come before tag-only match
        $exactIndex = array_search($titleExactEntry->id, $matchedIds);
        $partialIndex = array_search($titlePartialEntry->id, $matchedIds);
        $tagOnlyIndex = array_search($tagOnlyEntry->id, $matchedIds);

        $this->assertNotFalse($exactIndex, 'Exact title match should be found');
        $this->assertNotFalse($partialIndex, 'Partial title match should be found');
        $this->assertNotFalse($tagOnlyIndex, 'Tag-only match should be found');

        $this->assertLessThan($tagOnlyIndex, $exactIndex, 'Title match must rank higher than tag-only match');
        $this->assertLessThan($tagOnlyIndex, $partialIndex, 'Title match must rank higher than tag-only match');

        // Also test the search controller endpoint
        $response = $this->getJson(route('search', ['q' => 'test']));
        $response->assertOk();
        $results = $response->json('results');

        $entryResults = array_filter($results, fn ($r) => ($r['type'] ?? '') === 'entry');
        $entryIds = array_column($entryResults, 'id');

        $this->assertLessThan(
            array_search($tagOnlyEntry->id, $entryIds),
            array_search($titleExactEntry->id, $entryIds)
        );
    }

    public function test_chat_interface_defaults_to_greeting_screen_without_forcing_old_conversation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create an existing conversation
        Conversation::create([
            'user_id' => $user->id,
            'title' => 'Previous Discussion',
            'type' => 'general',
            'message_count' => 3,
            'last_activity' => now(),
        ]);

        $component = Livewire::test(ChatInterface::class)
            ->assertSet('activeSession', null)
            ->assertSet('messages', [])
            ->assertSeeHtml('AI Reflection Companion');

        $html = html_entity_decode($component->html(), ENT_QUOTES);
        $this->assertTrue(
            collect(['Ready when you are', 'What\'s on your mind?', 'Take your time', 'Here whenever you\'re ready', "Let's talk through it", 'A quiet space for your thoughts', 'Shoot your shot', 'Hop in'])
                ->contains(fn ($phrase) => str_contains($html, $phrase)),
            'Expected one of the greeting pool phrases in empty chat screen'
        );
    }

    public function test_deleting_conversation_resets_to_greeting_screen_instantaneously()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $convo = Conversation::create([
            'user_id' => $user->id,
            'title' => 'To Be Deleted',
            'type' => 'general',
            'message_count' => 1,
            'last_activity' => now(),
        ]);

        Livewire::test(ChatInterface::class, ['conversation' => $convo->id])
            ->call('selectSession', $convo->id)
            ->assertSet('activeSession.id', $convo->id)
            ->call('deleteSession', $convo->id)
            ->assertSet('activeSession', null)
            ->assertSet('messages', []);

        $this->assertDatabaseMissing('conversations', ['id' => $convo->id]);
    }

    public function test_short_title_and_content_are_allowed_when_creating_and_editing_entries()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. NewEntry accepts short 1-char title and short 3-char content
        Livewire::test(\App\Livewire\NewEntry::class)
            ->set('title', 'A')
            ->set('content', 'Hey')
            ->call('save')
            ->assertHasNoErrors(['title', 'content']);

        $entry = Entry::where('user_id', $user->id)->first();
        $this->assertNotNull($entry);
        $this->assertEquals('A', $entry->title);
        $this->assertEquals('Hey', $entry->content);

        // 2. EditEntry accepts short title and content without throwing min length validation
        Livewire::test(\App\Livewire\EditEntry::class, ['entry' => $entry])
            ->set('title', 'B')
            ->set('content', 'Ok')
            ->call('save')
            ->assertHasNoErrors(['title', 'content']);

        $entry->refresh();
        $this->assertEquals('B', $entry->title);
        $this->assertEquals('Ok', $entry->content);
    }

    public function test_weekly_summary_prompt_contains_no_emojis_and_uses_executive_template()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Entry::create([
            'user_id' => $user->id,
            'title' => 'Launch day',
            'content' => 'Getting ready to launch features.',
            'created_at' => now()->subDay(),
        ]);

        $capturedPrompt = null;
        $mockAi = $this->mock(\App\Services\AiChatService::class);
        $mockAi->shouldReceive('generateResponse')
            ->once()
            ->withArgs(function ($prompt) use (&$capturedPrompt) {
                $capturedPrompt = $prompt;
                return true;
            })
            ->andReturn("## Executive Overview\nWeekly review summary.\n\n| Dimension | Focus |\n| --- | --- |\n| Theme | Launch |");

        Livewire::test(\App\Livewire\Dashboard\AiQuickChat::class)
            ->call('summarizePastWeek')
            ->call('loadWeeklySummary')
            ->assertSet('weeklySummary', "## Executive Overview\nWeekly review summary.\n\n| Dimension | Focus |\n| --- | --- |\n| Theme | Launch |")
            ->assertSee('Executive Overview');

        $this->assertNotNull($capturedPrompt);
        // Ensure prompt explicitly forbids emojis and bullet points, and contains executive report sections
        $this->assertStringContainsStringIgnoringCase('Do NOT use ANY emojis', $capturedPrompt);
        $this->assertStringContainsStringIgnoringCase('Do NOT use bullet points', $capturedPrompt);
        $this->assertStringContainsString('Executive Overview', $capturedPrompt);
        $this->assertStringContainsString('Key Dimensions', $capturedPrompt);
        // Verify no emojis like 🧭, ✨, 🔁, 💡, 🚀 in the prompt
        $this->assertStringNotContainsString('🧭', $capturedPrompt);
        $this->assertStringNotContainsString('✨', $capturedPrompt);
        $this->assertStringNotContainsString('🔁', $capturedPrompt);
        $this->assertStringNotContainsString('💡', $capturedPrompt);
        $this->assertStringNotContainsString('🚀', $capturedPrompt);
    }

    public function test_banner_upload_view_has_no_background_uploading_indicator_and_saves_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Verify template has zero references to "Uploading in background…" or uploading badge
        $viewContent = file_get_contents(resource_path('views/livewire/new-entry.blade.php'));
        $this->assertStringNotContainsString('Uploading in background', $viewContent);
        $this->assertStringNotContainsString('Uploading…', $viewContent);

        // Verify NewEntry component renders cleanly and saves with banner
        \Illuminate\Support\Facades\Storage::fake('public');
        $fakeFile = \Illuminate\Http\UploadedFile::fake()->image('nature.jpg', 600, 400);

        Livewire::test(\App\Livewire\NewEntry::class)
            ->set('title', 'Mountain Trip')
            ->set('content', 'Had a great hike today.')
            ->set('banner', $fakeFile)
            ->call('save')
            ->assertHasNoErrors();

        $entry = Entry::where('user_id', $user->id)->first();
        $this->assertNotNull($entry);
        $this->assertEquals('Mountain Trip', $entry->title);
        $this->assertNotNull($entry->banner_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($entry->banner_path);
    }

    public function test_chat_interface_no_loading_conversation_text_and_session_selection_resets()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $chatBlade = file_get_contents(resource_path('views/livewire/chat-interface.blade.php'));
        // Verify no "Loading conversation..." or "Loading messages..." text is present
        $this->assertStringNotContainsString('Loading conversation...', $chatBlade);
        $this->assertStringNotContainsString('Loading messages...', $chatBlade);
        // Verify createNewSession is not targeted by main overlay
        $this->assertStringNotContainsString('wire:target="selectSession, createNewSession"', $chatBlade);

        $c1 = Conversation::create([
            'user_id' => $user->id,
            'title' => 'Chat One',
            'type' => 'general',
            'message_count' => 1,
            'last_activity' => now(),
        ]);
        $c2 = Conversation::create([
            'user_id' => $user->id,
            'title' => 'Chat Two',
            'type' => 'general',
            'message_count' => 2,
            'last_activity' => now(),
        ]);

        Livewire::test(ChatInterface::class)
            ->call('selectSession', $c1->id)
            ->assertSet('activeSession.id', $c1->id)
            ->assertDispatched('session-selected')
            ->call('createNewSession')
            ->assertSet('activeSession', null)
            ->assertDispatched('session-cleared')
            ->call('selectSession', $c2->id)
            ->assertSet('activeSession.id', $c2->id)
            ->assertDispatched('session-selected');
    }

    public function test_edit_entry_saves_new_tag_and_displays_it()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entry = Entry::create([
            'user_id' => $user->id,
            'title' => 'Daily Reflections',
            'content' => 'Writing about today.',
        ]);

        // 1. User types in newTag and clicks save (even without pressing Enter)
        Livewire::test(\App\Livewire\EditEntry::class, ['entry' => $entry])
            ->set('newTag', 'mindfulness')
            ->call('save')
            ->assertSee('#mindfulness')
            ->assertSet('isEditing', false);

        $this->assertDatabaseHas('tags', ['name' => 'mindfulness']);
        $this->assertTrue($entry->fresh()->tags->contains('name', 'mindfulness'));

        // 2. User adds tag with leading hash
        Livewire::test(\App\Livewire\EditEntry::class, ['entry' => $entry])
            ->set('newTag', '#wellness')
            ->call('addTag')
            ->call('save')
            ->assertSee('#wellness');

        $this->assertTrue($entry->fresh()->tags->contains('name', 'wellness'));
    }

    public function test_mid_convo_chat_persists_and_restores_on_refresh()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => 'Ongoing Session',
            'type' => 'general',
            'message_count' => 1,
            'last_activity' => now(),
        ]);

        \App\Models\Message::create([
            'conversation_id' => $conversation->id,
            'content' => 'Hello Lumi!',
            'is_ai_response' => false,
        ]);

        // 1. When user selects or creates session, active_chat_conversation_id is recorded in session
        Livewire::test(ChatInterface::class)
            ->call('selectSession', $conversation->id)
            ->assertSet('activeSession.id', $conversation->id)
            ->assertDispatched('session-selected');

        $this->assertSame($conversation->id, session('active_chat_conversation_id'));

        // 2. On page refresh without query param, mount() restores the mid-convo session automatically
        Livewire::test(ChatInterface::class)
            ->assertSet('activeSession.id', $conversation->id)
            ->assertSeeHtml('Hello Lumi!')
            ->assertDontSeeHtml('Ready when you are');

        // 3. Explicit query param also restores the conversation
        $this->withServerVariables(['QUERY_STRING' => 'conversation='.$conversation->id]);
        Livewire::withQueryParams(['conversation' => $conversation->id])
            ->test(ChatInterface::class)
            ->assertSet('activeSession.id', $conversation->id);
    }

    public function test_greeting_ui_does_not_render_inside_active_conversation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // When starting message, ensureSessionForMessage creates conversation
        $chat = Livewire::test(ChatInterface::class);

        // Initially no active session: greeting is present
        $this->assertNull($chat->get('activeSession'));

        // Once session is started, active session is set and messages container does NOT have greeting
        $chat->call('ensureSessionForMessage');
        $this->assertNotNull($chat->get('activeSession'));
        $this->assertNotEmpty($chat->get('activeSession')['id']);

        // Verify that starter prompts and greeting do not render in the active conversation area
        $chat->assertDontSeeHtml('id="empty-chat-state"');
    }
}
