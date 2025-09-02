<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class BlogController extends Controller
{
    private $rssSources = [
        'Psychology Today' => 'https://www.psychologytoday.com/us/blog/rss',
        'Mindful' => 'https://www.mindful.org/feed/',
        'Headspace' => 'https://www.headspace.com/blog/feed/',
        'The Mighty' => 'https://themighty.com/feed/',
        'Zen Habits' => 'https://zenhabits.net/feed/'
    ];

    public function index(Request $request)
    {
        $blogs = $this->getCachedBlogs();

        if ($request->has('category') && $request->category) {
            $blogs = $blogs->where('category', $request->category);
        }

        if ($request->has('source') && $request->source) {
            $blogs = $blogs->where('source_name', $request->source);
        }

        $blogs = $blogs->recent()->paginate(12);

        $categories = Blog::distinct('category')->whereNotNull('category')->pluck('category');
        $sources = Blog::distinct('source_name')->pluck('source_name');

        return view('blogs.index', compact('blogs', 'categories', 'sources'));
    }

    public function refresh()
    {
        $this->fetchLatestBlogs();

        return redirect()->route('blogs.index')->with('success', 'Blogs refreshed successfully!');
    }

    private function getCachedBlogs()
    {
        $freshBlogs = Blog::where('cached_at', '>', now()->subHours(24))->count();

        if ($freshBlogs < 10) {
            $this->fetchLatestBlogs();
        }

        return Blog::query();
    }

    private function fetchLatestBlogs()
    {
        foreach ($this->rssSources as $sourceName => $rssUrl) {
            try {
                $this->parseRssFeed($sourceName, $rssUrl);
            } catch (\Exception $e) {
                \Log::warning("Failed to fetch RSS from {$sourceName}: " . $e->getMessage());
            }
        }
    }

    private function parseRssFeed($sourceName, $rssUrl)
    {
        try {
            $response = Http::timeout(30)->get($rssUrl);

            if (!$response->successful()) {
                return;
            }

            $xml = simplexml_load_string($response->body());

            if (!$xml || !isset($xml->channel->item)) {
                return;
            }

            foreach ($xml->channel->item as $item) {
                $this->storeBlogItem($sourceName, $item, $rssUrl);
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

        $imageUrl = null;

        // 1. enclosure
        if (isset($item->enclosure) && isset($item->enclosure['url'])) {
            $imageUrl = (string) $item->enclosure['url'];
        }

        // 2. media:content
        elseif (isset($item->children('media', true)->content)) {
            $media = $item->children('media', true)->content;
            if (isset($media->attributes()['url'])) {
                $imageUrl = (string) $media->attributes()['url'];
            }
        }

        // 3. content:encoded
        elseif (isset($item->{'content:encoded'})) {
            $contentEncoded = (string) $item->{'content:encoded'};
            if (preg_match('/<figure[^>]*class="article__featured"[^>]*>.*?<img[^>]+src=["\'](?P<src>[^"\']+)["\']/is', $contentEncoded, $matches)) {
                $imageUrl = $matches['src'];
            } elseif (preg_match('/<img.+src=["\'](?P<src>.+?)["\']/i', $contentEncoded, $matches)) {
                $imageUrl = $matches['src'];
            }
        }

        // 4. description <img>
        if (!$imageUrl && preg_match('/<img.+src=["\'](?P<src>.+?)["\']/i', (string) $item->description, $matches)) {
            $imageUrl = $matches['src'];
        }

        // 5. Fallback: scrape external page og:image
        if (!$imageUrl && $externalUrl) {
            try {
                $html = Http::timeout(15)->get($externalUrl)->body();

                // First try og:image
                if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
                    $imageUrl = $matches[1];
                }
                // If no OG tag, try figure.article__featured
                elseif (preg_match('/<figure[^>]*class=["\']article__featured["\'][^>]*>.*?<img[^>]+src=["\']([^"\']+)["\']/is', $html, $matches)) {
                    $imageUrl = $matches[1];
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to fetch image from {$externalUrl}: " . $e->getMessage());
            }
        }

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
            'journaling',
            'writing',
            'reflection',
            'mindfulness',
            'meditation',
            'anxiety',
            'depression',
            'stress',
            'therapy',
            'wellness',
            'self-care',
            'mental health',
            'emotional',
            'healing',
            'growth'
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
}
