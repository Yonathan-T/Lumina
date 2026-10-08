<?php

namespace App\Livewire;

use App\Models\Blog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Livewire\Component;

class BlogLoader extends Component
{
    public $blogs = [];

    public $isLoading = false;

    public $selectedCategory = '';

    public $selectedSource = '';

    public $categories = [];

    public $sources = [];

    // UI state
    public $showPreview = true;

    private $rssSources = [
        'Tiny Buddha' => 'https://tinybuddha.com/feed/',
        'WriteWell' => 'https://www.writewellcommunity.com/feed/',
        'Becoming Minimalist' => 'https://www.becomingminimalist.com/feed/',
    ];

    private $fallbackImages = [
        // 'Mindful' => 'https://images.unsplash.com/photo-1544367563-12123d8965cd?q=80&w=800&auto=format&fit=crop',
        'WriteWell' => 'https://images.unsplash.com/photo-1517842645767-c639042777db?q=80&w=800&auto=format&fit=crop',
        'Becoming Minimalist' => 'https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?q=80&w=800&auto=format&fit=crop',
        'Tiny Buddha' => 'https://images.unsplash.com/photo-1528319725582-ddc096101511?q=80&w=800&auto=format&fit=crop',
    ];

    public function mount()
    {
        $this->loadCachedData();

        // Only trigger initial load if we have very little data
        if (count($this->blogs) < 4) {
            $this->isLoading = true;
            $this->dispatch('start-blog-loading');
        }
    }

    public function loadCachedData()
    {
        $this->blogs = Cache::remember('blogs:index:latest', 300, function () {
            return Blog::query()
                ->select([
                    'id',
                    'title',
                    'description',
                    'external_url',
                    'source_name',
                    'published_at',
                    'category',
                    'tags',
                    'image_url',
                ])
                ->orderBy('published_at', 'desc')
                ->take(24)
                ->get()
                ->toArray();
        });

        $this->categories = Cache::remember('blogs:index:categories', 300, function () {
            return Blog::query()
                ->whereNotNull('category')
                ->orderBy('category')
                ->distinct()
                ->pluck('category')
                ->toArray();
        });

        $this->sources = Cache::remember('blogs:index:sources', 300, function () {
            return Blog::query()
                ->orderBy('source_name')
                ->distinct()
                ->pluck('source_name')
                ->toArray();
        });
    }

    #[On('start-blog-loading')]
    public function startAsyncLoading()
    {
        \Log::info('Async blog loading started');
        $this->isLoading = true;

        // release session lock so UI remains responsive during long fetches
        if (session_id()) {
            session_write_close();
            \Log::info('Session lock released');
        }

        foreach ($this->rssSources as $sourceName => $rssUrl) {
            \Log::info("Fetching RSS: {$sourceName}");
            try {
                $this->parseRssFeed($sourceName, $rssUrl);
            } catch (\Exception $e) {
                \Log::warning("RSS fetch failed for {$sourceName}: ".$e->getMessage());
            }
        }

        Cache::forget('blogs:index:latest');
        Cache::forget('blogs:index:categories');
        Cache::forget('blogs:index:sources');
        $this->loadCachedData();
        $this->isLoading = false;
        $this->showPreview = false;
        \Log::info('Async blog loading completed');
    }

    private function parseRssFeed($sourceName, $rssUrl)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ])->timeout(10)->get($rssUrl);

            if (! $response->successful()) {
                return;
            }

            $xml = simplexml_load_string($response->body());
            if (! $xml || ! isset($xml->channel->item)) {
                return;
            }

            $count = 0;
            foreach ($xml->channel->item as $item) {
                if ($count >= 6) {
                    break;
                }
                $this->storeBlogItem($sourceName, $item, $rssUrl);
                $count++;
            }
        } catch (\Exception $e) {
            \Log::error('RSS parsing error: '.$e->getMessage());
        }
    }

    private function storeBlogItem($sourceName, $item, $sourceUrl)
    {
        $externalUrl = (string) $item->link;

        if (Blog::where('external_url', $externalUrl)->exists()) {
            return;
        }

        $title = (string) $item->title;
        $description = (string) ($item->description ?? $item->summary ?? '');
        $description = strip_tags($description);
        $description = html_entity_decode($description);

        $imageUrl = $this->extractImageUrl($item, $externalUrl);

        if (! $imageUrl && isset($this->fallbackImages[$sourceName])) {
            $imageUrl = $this->fallbackImages[$sourceName];
        }

        Blog::create([
            'title' => $title,
            'description' => mb_substr($description, 0, 500),
            'external_url' => $externalUrl,
            'source_name' => $sourceName,
            'source_url' => $sourceUrl,
            'published_at' => isset($item->pubDate) ? Carbon::parse((string) $item->pubDate) : now(),
            'category' => $this->categorizeContent($title.' '.$description),
            'tags' => [],
            'image_url' => $imageUrl,
            'cached_at' => now(),
        ]);
    }

    private function extractImageUrl($item, $externalUrl)
    {
        // Try enclosure
        if (isset($item->enclosure) && isset($item->enclosure['url'])) {
            return (string) $item->enclosure['url'];
        }

        // Try media:content
        $media = $item->children('media', true);
        if (isset($media->content) && isset($media->content->attributes()['url'])) {
            return (string) $media->content->attributes()['url'];
        }

        // Try regex on description
        if (preg_match('/<img[^>]+src=["\'](?P<src>[^"\']+)["\']/i', (string) $item->description, $matches)) {
            return $matches['src'];
        }

        // Try content:encoded
        $namespaces = $item->getNamespaces(true);
        if (isset($namespaces['content'])) {
            $contentEncoded = (string) $item->children($namespaces['content'])->encoded;
            if (preg_match('/<img[^>]+src=["\'](?P<src>[^"\']+)["\']/i', $contentEncoded, $matches)) {
                return $matches['src'];
            }
        }

        return null;
    }

    private function categorizeContent($content)
    {
        $content = strtolower($content);
        if (str_contains($content, 'anxiety') || str_contains($content, 'stress')) {
            return 'Anxiety & Stress';
        }
        if (str_contains($content, 'depression') || str_contains($content, 'mood')) {
            return 'Depression & Mood';
        }
        if (str_contains($content, 'journal') || str_contains($content, 'writing')) {
            return 'Journaling & Writing';
        }
        if (str_contains($content, 'mindful') || str_contains($content, 'meditation')) {
            return 'Mindfulness & Meditation';
        }

        return 'Mental Wellness';
    }

    public function triggerFreshLoad()
    {
        \Log::info('Manual fresh load started');
        $this->isLoading = true;
        $this->blogs = [];

        // release session lock so UI remains responsive
        if (session_id()) {
            session_write_close();
        }

        foreach ($this->rssSources as $sourceName => $rssUrl) {
            \Log::info("Manual fetch: {$sourceName}");
            try {
                $this->parseRssFeed($sourceName, $rssUrl);
            } catch (\Exception $e) {
                \Log::warning("RSS fetch failed for {$sourceName}: ".$e->getMessage());
            }
        }

        Cache::forget('blogs:index:latest');
        Cache::forget('blogs:index:categories');
        Cache::forget('blogs:index:sources');
        $this->loadCachedData();
        $this->isLoading = false;
        \Log::info('Manual fresh load completed');
    }

    public function getFormattedBlogs(): array
    {
        $fallbackImages = [
            'https://images.unsplash.com/photo-1506126613408-eca07ce68773?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1544367563-12123d8965cd?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1517842645767-c639042777db?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1528319725582-ddc096101511?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1508672019048-805b876b67e2?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1515377905703-c4788e51af15?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1499750310107-5fef28a66643?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1470240731273-7821a6eeb6bd?q=80&w=1000&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1516541196182-6bdb0516ed27?q=80&w=1000&auto=format&fit=crop',
        ];

        return array_map(function ($blog, $index) use ($fallbackImages) {
            $feedImage = !empty($blog['image_url']) 
                ? $blog['image_url'] 
                : $fallbackImages[$index % count($fallbackImages)];
            $rawTags = is_array($blog['tags'] ?? null) 
                ? $blog['tags'] 
                : (json_decode($blog['tags'] ?? '[]', true) ?: []);
            if (empty($rawTags) && !empty($blog['category'])) {
                $rawTags = array_slice(array_filter(explode(' ', strtolower(str_replace(['&', 'and', ',', '/'], '', $blog['category'])))), 0, 2);
            }
            $publishedDate = isset($blog['published_at']) ? Carbon::parse($blog['published_at']) : now();
            $readMinutes = max(2, min(7, (int) ceil(str_word_count(($blog['title'] ?? '') . ' ' . ($blog['description'] ?? '')) / 25) + 1));

            return [
                'id' => (string) ($blog['id'] ?? $index),
                'title' => (string) ($blog['title'] ?? ''),
                'description' => (string) ($blog['description'] ?? ''),
                'external_url' => (string) ($blog['external_url'] ?? '#'),
                'source_name' => (string) ($blog['source_name'] ?? 'Mindful'),
                'category' => (string) ($blog['category'] ?? ''),
                'image' => (string) $feedImage,
                'fallback' => (string) $fallbackImages[($index + 2) % count($fallbackImages)],
                'tags' => array_values(array_slice($rawTags, 0, 3)),
                'date_formatted' => $publishedDate->diffForHumans(),
                'date_title' => $publishedDate->format('M d, Y'),
                'read_time' => $readMinutes . 'm',
                'code' => '# ' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
            ];
        }, $this->blogs, array_keys($this->blogs));
    }

    public function render()
    {
        return view('livewire.blog-loader', [
            'formattedBlogs' => $this->getFormattedBlogs(),
        ]);
    }
}
