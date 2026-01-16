<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Blog;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

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
        $query = Blog::query();
        
        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }
        
        if ($this->selectedSource) {
            $query->where('source_name', $this->selectedSource);
        }
        
        $this->blogs = $query->orderBy('published_at', 'desc')->take(12)->get()->toArray();
        $this->categories = Blog::distinct('category')->whereNotNull('category')->pluck('category')->toArray();
        $this->sources = Blog::distinct('source_name')->pluck('source_name')->toArray();
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
                \Log::warning("RSS fetch failed for {$sourceName}: " . $e->getMessage());
            }
        }

        $this->loadCachedData();
        $this->isLoading = false;
        $this->showPreview = false;
        \Log::info('Async blog loading completed');
    }

    private function parseRssFeed($sourceName, $rssUrl)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->timeout(10)->get($rssUrl);

            if (!$response->successful()) return;

            $xml = simplexml_load_string($response->body());
            if (!$xml || !isset($xml->channel->item)) return;

            $count = 0;
            foreach ($xml->channel->item as $item) {
                if ($count >= 6) break; 
                $this->storeBlogItem($sourceName, $item, $rssUrl);
                $count++;
            }
        } catch (\Exception $e) {
            \Log::error("RSS parsing error: " . $e->getMessage());
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
        
        if (!$imageUrl && isset($this->fallbackImages[$sourceName])) {
            $imageUrl = $this->fallbackImages[$sourceName];
        }

        Blog::create([
            'title' => $title,
            'description' => mb_substr($description, 0, 500),
            'external_url' => $externalUrl,
            'source_name' => $sourceName,
            'source_url' => $sourceUrl,
            'published_at' => isset($item->pubDate) ? Carbon::parse((string) $item->pubDate) : now(),
            'category' => $this->categorizeContent($title . ' ' . $description),
            'tags' => [], 
            'image_url' => $imageUrl,
            'cached_at' => now()
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
            $contentEncoded = (string)$item->children($namespaces['content'])->encoded;
            if (preg_match('/<img[^>]+src=["\'](?P<src>[^"\']+)["\']/i', $contentEncoded, $matches)) {
                return $matches['src'];
            }
        }

        return null;
    }

    private function categorizeContent($content)
    {
        $content = strtolower($content);
        if (str_contains($content, 'anxiety') || str_contains($content, 'stress')) return 'Anxiety & Stress';
        if (str_contains($content, 'depression') || str_contains($content, 'mood')) return 'Depression & Mood';
        if (str_contains($content, 'journal') || str_contains($content, 'writing')) return 'Journaling & Writing';
        if (str_contains($content, 'mindful') || str_contains($content, 'meditation')) return 'Mindfulness & Meditation';
        return 'Mental Wellness';
    }

    public function filterByCategory($category)
    {
        $this->selectedCategory = $category;
        $this->loadCachedData();
    }

    public function filterBySource($source)
    {
        $this->selectedSource = $source;
        $this->loadCachedData();
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
                \Log::warning("RSS fetch failed for {$sourceName}: " . $e->getMessage());
            }
        }

        $this->loadCachedData();
        $this->isLoading = false;
        \Log::info('Manual fresh load completed');
    }

    public function render()
    {
        return view('livewire.blog-loader');
    }
}
