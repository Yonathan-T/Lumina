<?php

namespace Tests\Feature;

use App\Livewire\TagList;
use App\Models\Entry;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class TagListPerformanceTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('avatar')->nullable();
            $table->text('api_key')->nullable();
            $table->timestamp('api_key_verified_at')->nullable();
            $table->text('elevenlabs_api_key')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::create('entry_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id');
            $table->foreignId('tag_id');
            $table->timestamps();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        $this->user = User::factory()->create();
    }

    /**
     * Test F14: Empirically verify N+1 query loop is eliminated via eager loading with whereIn.
     */
    public function test_tag_list_eliminates_n_plus_one_queries_when_loading_entries_with_tags(): void
    {
        $this->actingAs($this->user);

        $mainTag = Tag::create(['name' => 'focus']);
        $tagWork = Tag::create(['name' => 'work']);
        $tagLife = Tag::create(['name' => 'life']);

        // Create 15 entries, each attached to 3 tags
        for ($i = 1; $i <= 15; $i++) {
            $entry = Entry::create([
                'user_id' => $this->user->id,
                'title' => "Journal Entry {$i}",
                'content' => "Thoughts and reflections for entry {$i}",
            ]);
            $entry->tags()->attach([$mainTag->id, $tagWork->id, $tagLife->id]);
        }

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $component = Livewire::test(TagList::class);

        $queriesBefore = count($queries);
        $queries = [];

        // Select the tag to load its entries
        $component->call('showTagEntries', $mainTag->id);

        $showQueries = $queries;

        // If N+1 existed, there would be 15 additional queries for tags (1 per entry)
        // With eager loading with(['tags:id,name']), all entry tags must be fetched in exactly ONE query using "in (...)"
        $eagerLoadedTagQueries = array_filter($showQueries, function ($sql) {
            return str_contains($sql, 'tags') && str_contains($sql, 'in (');
        });

        $this->assertCount(1, $eagerLoadedTagQueries, 'Eager loading must execute exactly 1 WHERE IN query for all entry tags.');
        $this->assertLessThanOrEqual(7, count($showQueries), 'Total queries during showTagEntries must not scale with entry count (O(1) queries).');

        // Confirm all 15 entries are displayed
        for ($i = 1; $i <= 15; $i++) {
            $component->assertSee("Journal Entry {$i}");
        }
        $component->assertSee('#work');
        $component->assertSee('#life');
    }

    /**
     * Test F14: Column projections are enforced in SQL queries (no SELECT * on entries/tags).
     */
    public function test_tag_list_projects_only_necessary_columns_in_sql(): void
    {
        $this->actingAs($this->user);

        $tag = Tag::create(['name' => 'projections']);
        $entry = Entry::create([
            'user_id' => $this->user->id,
            'title' => 'Projection Test',
            'content' => 'Checking explicit column projections',
        ]);
        $entry->tags()->attach($tag->id);

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        Livewire::test(TagList::class)
            ->call('showTagEntries', $tag->id);

        // Find entry query
        $entryQueries = array_filter($queries, function ($sql) {
            return str_contains($sql, 'select "entries"."id"');
        });

        $this->assertNotEmpty($entryQueries);
        $entrySql = reset($entryQueries);

        // Verify explicit projections on entries table
        $this->assertStringContainsString('"entries"."id"', $entrySql);
        $this->assertStringContainsString('"entries"."title"', $entrySql);
        $this->assertStringContainsString('"entries"."content"', $entrySql);
        $this->assertStringContainsString('"entries"."created_at"', $entrySql);
        $this->assertStringContainsString('"entries"."user_id"', $entrySql);

        // Find tag eager load query
        $tagEagerQueries = array_filter($queries, function ($sql) {
            return str_contains($sql, 'where "entry_tag"."entry_id" in');
        });

        $this->assertNotEmpty($tagEagerQueries);
        $tagEagerSql = reset($tagEagerQueries);

        // Verify explicit projections on tags eager load
        $this->assertStringContainsString('"tags"."id"', $tagEagerSql);
        $this->assertStringContainsString('"tags"."name"', $tagEagerSql);
    }

    /**
     * Test F15: $tagEntries is NOT serialized into the Livewire component snapshot payload.
     */
    public function test_tag_list_does_not_serialize_tag_entries_in_livewire_snapshot(): void
    {
        $this->actingAs($this->user);

        $tag = Tag::create(['name' => 'payload-slashed']);

        // Create 20 large entries
        for ($i = 1; $i <= 20; $i++) {
            $entry = Entry::create([
                'user_id' => $this->user->id,
                'title' => "Large Journal Entry #{$i}",
                'content' => str_repeat("Extensive reflection content detailing user thoughts, feelings, patterns and life events. ", 15),
            ]);
            $entry->tags()->attach($tag->id);
        }

        $component = Livewire::test(TagList::class);
        $component->call('showTagEntries', $tag->id);

        $snapshotData = $component->snapshot['data'];

        // F15: tagEntries MUST NOT exist as a state property in snapshot
        $this->assertArrayNotHasKey('tagEntries', $snapshotData, 'tagEntries must not be serialized into Livewire snapshot data.');

        // Snapshot data must only contain lightweight scalar state
        $this->assertEquals(['sort', 'queryString', 'selectedTagName', 'selectedTagCount', 'selectedTagId', 'paginators'], array_keys($snapshotData));

        // The full serialized snapshot payload must remain tiny (< 1000 bytes) despite 20 large entries
        $payloadJson = json_encode($component->snapshot);
        $this->assertLessThan(1024, strlen($payloadJson), 'Livewire snapshot payload must remain under 1KB.');

        // But the rendered HTML still displays the entries
        $component->assertSee('Large Journal Entry #1');
        $component->assertSee('Large Journal Entry #20');
    }

    /**
     * Test F16: Declarative wire:loading indicators exist for tag selection.
     */
    public function test_tag_list_renders_wire_loading_indicators_for_tag_selection(): void
    {
        $this->actingAs($this->user);

        $tag = Tag::create(['name' => 'ui-loading']);
        $entry = Entry::create([
            'user_id' => $this->user->id,
            'title' => 'UI Loading Entry',
            'content' => 'Content for loading test',
        ]);
        $entry->tags()->attach($tag->id);

        $component = Livewire::test(TagList::class);
        $html = $component->html();

        // Tag button loading feedback
        $this->assertStringContainsString('wire:loading.attr="disabled"', $html);
        $this->assertStringContainsString('wire:loading.class="opacity-60 cursor-wait"', $html);
        $this->assertStringContainsString('wire:target="showTagEntries"', $html);

        // Spinner SVG inside tag button
        $this->assertStringContainsString('wire:loading', $html);
        $this->assertStringContainsString('wire:target="showTagEntries(' . $tag->id . ')"', $html);

        // Dedicated status loading spinner
        $this->assertStringContainsString('wire:loading wire:target="showTagEntries"', $html);
    }
}
