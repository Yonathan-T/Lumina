<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\EntryEmbedding;
use App\Models\Tag;
use App\Models\User;
use App\Services\EmbeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmbeddingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cosine_similarity_math(): void
    {
        // Identical vectors -> 1.0
        $this->assertEqualsWithDelta(1.0, EmbeddingService::cosineSimilarity([1.0, 0.0], [1.0, 0.0]), 0.0001);

        // Orthogonal vectors -> 0.0
        $this->assertEqualsWithDelta(0.0, EmbeddingService::cosineSimilarity([1.0, 0.0], [0.0, 1.0]), 0.0001);

        // Opposite vectors -> -1.0
        $this->assertEqualsWithDelta(-1.0, EmbeddingService::cosineSimilarity([1.0, 0.0], [-1.0, 0.0]), 0.0001);

        // Partial match
        $sim = EmbeddingService::cosineSimilarity([1.0, 1.0], [1.0, 0.0]);
        $this->assertEqualsWithDelta(0.7071, $sim, 0.001);
    }

    public function test_prepare_text_for_entry_formats_title_tags_and_content(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'title' => 'Trip to Kyoto',
            'content' => 'Visited ancient temples in Arashiyama.',
        ]);

        $tag = Tag::create(['name' => 'travel', 'slug' => 'travel']);
        $entry->tags()->attach($tag->id);
        $entry->load('tags');

        $service = new EmbeddingService();
        $prepared = $service->prepareTextForEntry($entry);

        $this->assertStringContainsString('Title: Trip to Kyoto', $prepared);
        $this->assertStringContainsString('Tags: travel', $prepared);
        $this->assertStringContainsString('Visited ancient temples in Arashiyama.', $prepared);
    }

    public function test_update_entry_embedding_persists_vector(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'embedding' => [
                    'values' => [0.12, 0.34, -0.56, 0.78],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'title' => 'Morning Jog',
            'content' => 'Ran 5 kilometers around the park.',
        ]);

        $service = new EmbeddingService();
        $embedding = $service->updateEntryEmbedding($entry);

        $this->assertNotNull($embedding);
        $this->assertDatabaseHas('entry_embeddings', [
            'entry_id' => $entry->id,
            'user_id' => $user->id,
            'dimensions' => 4,
        ]);

        $this->assertEquals([0.12, 0.34, -0.56, 0.78], $embedding->embedding);
    }

    public function test_find_semantic_matches_ranks_by_similarity(): void
    {
        $user = User::factory()->create();

        $entryA = Entry::create([
            'user_id' => $user->id,
            'title' => 'Marathon Prep',
            'content' => 'Running and jogging every morning.',
        ]);

        $entryB = Entry::create([
            'user_id' => $user->id,
            'title' => 'Cooking Pasta',
            'content' => 'Making Italian tomato sauce from scratch.',
        ]);

        // Mock vectors: Query is close to entryA, distant from entryB
        EntryEmbedding::create([
            'user_id' => $user->id,
            'entry_id' => $entryA->id,
            'model' => 'gemini-embedding-001',
            'dimensions' => 3,
            'embedding' => [1.0, 0.0, 0.0],
        ]);

        EntryEmbedding::create([
            'user_id' => $user->id,
            'entry_id' => $entryB->id,
            'model' => 'gemini-embedding-001',
            'dimensions' => 3,
            'embedding' => [0.0, 1.0, 0.0],
        ]);

        // Mock query embedding to be [0.95, 0.05, 0.0] (very close to entryA)
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'embedding' => [
                    'values' => [0.95, 0.05, 0.0],
                ],
            ], 200),
        ]);

        $service = new EmbeddingService();
        $matches = $service->findSemanticMatches($user->id, 'running training', 5, 0.50, $user);

        $this->assertCount(1, $matches);
        $this->assertEquals($entryA->id, $matches->first()['entry_id']);
        $this->assertGreaterThan(0.9, $matches->first()['score']);
    }

    public function test_find_related_entries_finds_contextually_similar_entries(): void
    {
        $user = User::factory()->create();

        $entrySource = Entry::create([
            'user_id' => $user->id,
            'title' => 'Deep Work Session',
            'content' => 'Focusing on software architecture.',
        ]);

        $entryRelated = Entry::create([
            'user_id' => $user->id,
            'title' => 'Coding Late at Night',
            'content' => 'Refactoring backend services and clean code.',
        ]);

        $entryUnrelated = Entry::create([
            'user_id' => $user->id,
            'title' => 'Baking Sourdough',
            'content' => 'Flour, water, and yeast fermentation.',
        ]);

        EntryEmbedding::create([
            'user_id' => $user->id,
            'entry_id' => $entrySource->id,
            'model' => 'gemini-embedding-001',
            'dimensions' => 3,
            'embedding' => [1.0, 0.2, 0.0],
        ]);

        EntryEmbedding::create([
            'user_id' => $user->id,
            'entry_id' => $entryRelated->id,
            'model' => 'gemini-embedding-001',
            'dimensions' => 3,
            'embedding' => [0.98, 0.22, 0.0],
        ]);

        EntryEmbedding::create([
            'user_id' => $user->id,
            'entry_id' => $entryUnrelated->id,
            'model' => 'gemini-embedding-001',
            'dimensions' => 3,
            'embedding' => [0.0, 0.0, 1.0],
        ]);

        $service = new EmbeddingService();
        $related = $service->findRelatedEntries($entrySource, 3, 0.60);

        $this->assertCount(1, $related);
        $this->assertEquals($entryRelated->id, $related->first()->id);
        $this->assertNotNull($related->first()->similarity_score);
        $this->assertGreaterThan(0.95, $related->first()->similarity_score);
    }
}
