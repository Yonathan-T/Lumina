<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Blog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class BlogLoader extends Component
{
    public $blogs = [];
    public $isLoading = true;
    public $showPreview = true;
    public $selectedCategory = '';
    public $selectedSource = '';
    public $categories = [];
    public $sources = [];
    public $loadingProgress = 0;
    public $currentLoadingSource = '';

    private $rssSources = [
        'Psychology Today' => 'https://www.psychologytoday.com/us/blog/rss',
        'Mindful' => 'https://www.mindful.org/feed/',
        'Headspace' => 'https://www.headspace.com/blog/feed/',
        'The Mighty' => 'https://themighty.com/feed/',
        'Zen Habits' => 'https://zenhabits.net/feed/'
    ];

    public function mount()
    {
        $this->loadCachedData();
        
        // If we have fresh cached data, show it immediately
        if (count($this->blogs) >= 10) {
            $this->isLoading = false;
            $this->showPreview = false;
        } else {
            // Start async loading
            $this->dispatch('start-blog-loading');
        }
    }

    private function loadCachedData()
    {
        $query = Blog::query();
        
        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }
        
        if ($this->selectedSource) {
            $query->where('source_name', $this->selectedSource);
        }
        
        $this->blogs = $query->recent()->take(12)->get()->toArray();
        $this->categories = Blog::distinct('category')->whereNotNull('category')->pluck('category')->toArray();
        $this->sources = Blog::distinct('source_name')->pluck('source_name')->toArray();
    }

    #[On('start-blog-loading')]
    public function startAsyncLoading()
    {
        $this->loadingProgress = 0;
        $totalSources = count($this->rssSources);
        $currentIndex = 0;

        foreach ($this->rssSources as $sourceName => $rssUrl) {
            $this->currentLoadingSource = $sourceName;
            $this->loadingProgress = round(($currentIndex / $totalSources) * 100);
            
            try {
                $this->parseRssFeed($sourceName, $rssUrl);
            } catch (\Exception $e) {
                \Log::warning("Failed to fetch RSS from {$sourceName}: " . $e->getMessage());
            }
            
            $currentIndex++;
            
            // Update progress and refresh view
            $this->loadingProgress = round(($currentIndex / $totalSources) * 100);
        }

        // Reload data after fetching
        $this->loadCachedData();
        $this->isLoading = false;
        $this->showPreview = false;
        $this->currentLoadingSource = '';
    }

    private function parseRssFeed($sourceName, $rssUrl)
    {
        try {
            $response = Http::timeout(15)->get($rssUrl);

            if (!$response->successful()) {
                return;
            }

            $xml = simplexml_load_string($response->body());

            if (!$xml || !isset($xml->channel->item)) {
                return;
            }

            $count = 0;
            foreach ($xml->channel->item as $item) {
                if ($count >= 5) break; // Limit to 5 items per source for faster loading
                
                $this->storeBlogItem($sourceName, $item, $rssUrl);
                $count++;
            }
        } catch (\Exception $e) {
            \Log::error("RSS parsing error for {$sourceName}: " . $e->getMessage());
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

        Blog::create([
            'title' => $title,
            'description' => substr($description, 0, 500),
            'external_url' => $externalUrl,
            'source_name' => $sourceName,
            'source_url' => $sourceUrl,
            'published_at' => isset($item->pubDate) ? Carbon::parse((string) $item->pubDate) : now(),
            'category' => $this->categorizeContent($title . ' ' . $description),
            'tags' => $this->extractTags($title . ' ' . $description),
            'image_url' => $imageUrl,
            'cached_at' => now()
        ]);
    }

    private function extractImageUrl($item, $externalUrl)
    {
        $imageUrl = null;

        // Try various methods to extract image
        if (isset($item->enclosure) && isset($item->enclosure['url'])) {
            $imageUrl = (string) $item->enclosure['url'];
        } elseif (isset($item->children('media', true)->content)) {
            $media = $item->children('media', true)->content;
            if (isset($media->attributes()['url'])) {
                $imageUrl = (string) $media->attributes()['url'];
            }
        } elseif (preg_match('/<img.+src=["\'](?P<src>.+?)["\']/i', (string) $item->description, $matches)) {
            $imageUrl = $matches['src'];
        }

        return $imageUrl;
    }

    private function categorizeContent($content)
    {
        $content = strtolower($content);

        if (str_contains($content, 'anxiety') || str_contains($content, 'stress')) {
            return 'Anxiety & Stress';
        } elseif (str_contains($content, 'depression') || str_contains($content, 'mood')) {
            return 'Depression & Mood';
        } elseif (str_contains($content, 'journal') || str_contains($content, 'writing')) {
            return 'Journaling & Writing';
        } elseif (str_contains($content, 'mindful') || str_contains($content, 'meditation')) {
            return 'Mindfulness & Meditation';
        } elseif (str_contains($content, 'therapy') || str_contains($content, 'counseling')) {
            return 'Therapy & Counseling';
        } else {
            return 'Mental Wellness';
        }
    }

    private function extractTags($content)
    {
        $keywords = [
            'journaling', 'writing', 'reflection', 'mindfulness', 'meditation',
            'anxiety', 'depression', 'stress', 'therapy', 'wellness',
            'self-care', 'mental health', 'emotional', 'healing', 'growth'
        ];

        $tags = [];
        $content = strtolower($content);

        foreach ($keywords as $keyword) {
            if (str_contains($content, $keyword)) {
                $tags[] = $keyword;
            }
        }

        return array_unique($tags);
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

    public function refreshBlogs()
    {
        $this->isLoading = true;
        $this->showPreview = true;
        $this->blogs = [];
        $this->dispatch('start-blog-loading');
    }

    public function render()
    {
        return view('livewire.blog-loader');
    }
}
