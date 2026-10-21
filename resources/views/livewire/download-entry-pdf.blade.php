<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $entry->title }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background-color: #0f172a; /* Dark background */
            color: #e2e8f0; /* Light text */
            margin: 0;
            padding: 40px;
        }
        .header-table {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 1px solid #334155;
            padding-bottom: 20px;
        }
        .logo-cell {
            width: 40px;
            vertical-align: middle;
        }
        .brand-cell {
            vertical-align: middle;
            padding-left: 10px;
            font-size: 24px;
            font-weight: bold;
            color: #f8fafc;
            letter-spacing: 1px;
        }
        .date-cell {
            text-align: right;
            vertical-align: middle;
            color: #94a3b8;
            font-size: 12px;
        }
        .banner-container {
            width: 100%;
            max-height: 240px;
            margin-bottom: 25px;
            border-radius: 8px;
            overflow: hidden;
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        .banner-img {
            width: 100%;
            max-height: 240px;
            display: block;
        }
        h1 {
            font-size: 32px;
            margin: 0 0 20px 0;
            color: #f8fafc;
            line-height: 1.2;
        }
        .stats-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .stat-box {
            background-color: #1e293b;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            width: 30%; /* Distribute space */
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #38bdf8; /* Light blue */
            display: block;
        }
        .stat-label {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
            display: block;
        }
        .spacer-cell {
            width: 5%;
        }
        .content {
            font-size: 14px;
            line-height: 1.8;
            color: #cbd5e1;
            text-align: left;
        }
        .content h1 { font-size: 20px; font-weight: bold; color: #f8fafc; margin-top: 20px; margin-bottom: 8px; }
        .content h2 { font-size: 17px; font-weight: bold; color: #f1f5f9; margin-top: 16px; margin-bottom: 6px; }
        .content h3 { font-size: 15px; font-weight: bold; color: #e2e8f0; margin-top: 14px; margin-bottom: 4px; }
        .content blockquote { border-left: 3px solid #6366f1; background-color: #1e293b; padding: 8px 12px; margin: 12px 0; font-style: italic; }
        .content pre { background-color: #0f172a; padding: 10px; border-radius: 6px; font-family: monospace; font-size: 12px; color: #34d399; }
        .content ul, .content ol { padding-left: 20px; margin: 10px 0; }
        .content li { margin-bottom: 4px; }
        .tags {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #334155;
        }
        .tag {
            display: inline-block;
            background-color: #1e293b;
            color: #38bdf8;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #64748b;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Header with Logo -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <!-- Base64 Encoded SVG Logo for PDF Compatibility -->
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2ZmZmZmZiI+PHBhdGggZD0iTTEzLDJsOSwxMy42TDEzLDIyWk0xMSwyLDIsMTUuNiwxMSwyMloiLz48L3N2Zz4=" 
                     width="40" height="40" style="transform: rotate(-45deg);">
            </td>
            <td class="brand-cell">
                LUMINA
            </td>
            <td class="date-cell">
                {{ $entry->created_at->format('M d, Y') }}
            </td>
        </tr>
    </table>

    <!-- Banner Image -->
    @php
        $bannerSrc = $entry->banner_base64 ?? (file_exists(public_path('storage/' . $entry->banner_path)) ? public_path('storage/' . $entry->banner_path) : $entry->banner_url);
    @endphp
    @if($entry->banner_path && $bannerSrc)
        <div class="banner-container">
            <img src="{{ $bannerSrc }}" class="banner-img">
        </div>
    @endif

    <!-- Title -->
    <h1>{{ $entry->title }}</h1>

    <!-- Compact Stats -->
    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ str_word_count($entry->content) }}</span>
                <span class="stat-label">Words</span>
            </td>
            <td class="spacer-cell"></td>
            <td class="stat-box">
                <span class="stat-value">{{ strlen($entry->content) }}</span>
                <span class="stat-label">Chars</span>
            </td>
            <td class="spacer-cell"></td>
            <td class="stat-box">
                <span class="stat-value">{{ $entry->tags->count() }}</span>
                <span class="stat-label">Tags</span>
            </td>
        </tr>
    </table>

    <!-- Content -->
    <div class="content">
        {!! \Illuminate\Support\Str::markdown($entry->content) !!}
    </div>

    <!-- Tags -->
    @if($entry->tags && $entry->tags->count() > 0)
        <div class="tags">
            @foreach($entry->tags as $tag)
                <span class="tag">#{{ $tag->name }}</span>
            @endforeach
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated by Lumina on {{ date('F j, Y') }}
    </div>
</body>
</html>