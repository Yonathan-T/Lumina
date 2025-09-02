<?php

namespace App\Console\Commands;

use App\Models\Blog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class FetchBlogContent extends Command
{
    protected $signature = 'blogs:fetch';
    protected $description = 'Fetch latest blog content from RSS feeds';

    private $rssSources = [
        'Psychology Today' => 'https://www.psychologytoday.com/us/blog/feed',
        'Mindful' => 'https://www.mindful.org/feed/',
        'Headspace' => 'https://www.headspace.com/meditation-articles/feed',
        'The Mighty' => 'https://themighty.com/feed/',
        'Zen Habits' => 'https://zenhabits.net/feed/'
    ];

    public function handle()
    {
        $this->info('Starting blog content fetch...');

        $totalFetched = 0;

        foreach ($this->rssSources as $sourceName => $rssUrl) {
            $this->info("Fetching from {$sourceName}...");

            try {
                $count = $this->parseRssFeed($sourceName, $rssUrl);
                $totalFetched += $count;
                $this->info("✓ Fetched {$count} articles from {$sourceName}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to fetch from {$sourceName}: " . $e->getMessage());
            }
        }

        // Clean up old cached content (older than 7 days)
        $deleted = Blog::where('cached_at', '<', now()->subDays(7))->delete();

        $this->info("Cleaned up {$deleted} old articles");
        $this->info("Total new articles fetched: {$totalFetched}");

        return Command::SUCCESS;
    }

    private function parseRssFeed($sourceName, $rssUrl)
    {
        $response = Http::timeout(30)->get($rssUrl);

        if (!$response->successful()) {
            throw new \Exception("HTTP request failed with status: " . $response->status());
        }

        $xml = simplexml_load_string($response->body());

        if (!$xml || !isset($xml->channel->item)) {
            throw new \Exception("Invalid RSS format");
        }

        $count = 0;

        foreach ($xml->channel->item as $item) {
            if ($this->storeBlogItem($sourceName, $item, $rssUrl)) {
                $count++;
            }
        }

        return $count;
    }

    private function storeBlogItem($sourceName, $item, $sourceUrl)
    {
        $externalUrl = (string) $item->link;

        // Skip if we already have this article
        if (Blog::where('external_url', $externalUrl)->exists()) {
            return false;
        }

        $title = (string) $item->title;
        $description = (string) ($item->description ?? $item->summary ?? '');

        // Clean HTML from description
        $description = strip_tags($description);
        $description = html_entity_decode($description);

        // Extract image from multiple sources
        $imageUrl = null;

        $this->info("Processing article: " . $title);

        // Try enclosure first (podcast/media RSS)
        if (isset($item->enclosure) && isset($item->enclosure['url'])) {
            $imageUrl = (string) $item->enclosure['url'];
            $this->info("Found image in enclosure: " . $imageUrl);
        }

        // Try media:content (media RSS namespace)
        if (!$imageUrl && isset($item->children('media', true)->content)) {
            $mediaContent = $item->children('media', true)->content;
            if (isset($mediaContent['url'])) {
                $imageUrl = (string) $mediaContent['url'];
                $this->info("Found image in media:content: " . $imageUrl);
            }
        }

        // Try media:thumbnail
        if (!$imageUrl && isset($item->children('media', true)->thumbnail)) {
            $mediaThumbnail = $item->children('media', true)->thumbnail;
            if (isset($mediaThumbnail['url'])) {
                $imageUrl = (string) $mediaThumbnail['url'];
                $this->info("Found image in media:thumbnail: " . $imageUrl);
            }
        }

        // Extract from description/content HTML
        if (!$imageUrl) {
            $rawDescription = (string) ($item->description ?? $item->summary ?? $item->content ?? '');
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $rawDescription, $matches)) {
                $imageUrl = $matches[1];
                $this->info("Found image in description HTML: " . $imageUrl);
            }
        }

        // Try content:encoded (WordPress RSS)
        if (!$imageUrl && isset($item->children('content', true)->encoded)) {
            $contentEncoded = (string) $item->children('content', true)->encoded;
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $contentEncoded, $matches)) {
                $imageUrl = $matches[1];
                $this->info("Found image in content:encoded: " . $imageUrl);
            }
        }

        // If still no image, try to scrape from the actual article page
        if (!$imageUrl && !empty($externalUrl)) {
            $this->info("No image found in RSS, attempting to scrape from: " . $externalUrl);
            $imageUrl = $this->scrapeImageFromUrl($externalUrl);
            if ($imageUrl) {
                $this->info("Scraped image: " . $imageUrl);
            } else {
                $this->info("No image found via scraping");
            }
        }

        // Determine category based on title/description keywords
        $category = $this->categorizeContent($title . ' ' . $description);

        // Extract tags based on content
        $tags = $this->extractTags($title . ' ' . $description);

        Blog::create([
            'title' => $title,
            'description' => substr($description, 0, 500),
            'external_url' => $externalUrl,
            'source_name' => $sourceName,
            'source_url' => $sourceUrl,
            'published_at' => isset($item->pubDate) ? Carbon::parse((string) $item->pubDate) : now(),
            'category' => $category,
            'tags' => $tags,
            'image_url' => $imageUrl,
            'cached_at' => now()
        ]);

        return true;
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

    /**
     * Scrape the main image from a given URL
     */
    private function scrapeImageFromUrl($url)
    {
        try {
            $this->info("Attempting to scrape image from URL: " . $url);

            // Only process certain domains to be efficient
            $allowedDomains = ['psychologytoday.com', 'mindful.org', 'headspace.com', 'themighty.com', 'zenhabits.net'];
            $isAllowed = false;

            foreach ($allowedDomains as $domain) {
                if (str_contains(parse_url($url, PHP_URL_HOST), $domain)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                $this->info("Domain not in allowed list for scraping: " . parse_url($url, PHP_URL_HOST));
                return null;
            }

            $this->info("Domain allowed, fetching page content...");

            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                ])
                ->get($url);

            if (!$response->successful()) {
                $this->info("HTTP request failed with status: " . $response->status());
                return null;
            }

            $html = $response->body();
            $this->info("Successfully fetched HTML content, length: " . strlen($html));

            // Look for Open Graph image first
            if (preg_match('/<meta\s+property="og:image"\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
                $this->info("Found Open Graph image: " . $matches[1]);
                return $matches[1];
            }

            // Look for Twitter card image
            if (preg_match('/<meta\s+name="twitter:image"\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
                $this->info("Found Twitter card image: " . $matches[1]);
                return $matches[1];
            }

            // Look for the first large image in the article
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
                $imageUrl = $matches[1];
                $this->info("Found first image in HTML: " . $imageUrl);

                // Make relative URLs absolute
                if (strpos($imageUrl, 'http') !== 0) {
                    $parsedUrl = parse_url($url);
                    $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                    $imageUrl = rtrim($baseUrl, '/') . '/' . ltrim($imageUrl, '/');
                    $this->info("Converted relative URL to absolute: " . $imageUrl);
                }

                return $imageUrl;
            }

            $this->info("No images found in HTML content");
            return null;

        } catch (\Exception $e) {
            $this->error("Error scraping image from {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract relevant tags from content
     */
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
