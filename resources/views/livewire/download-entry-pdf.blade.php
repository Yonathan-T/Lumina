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
            height: 256px; /* h-64 */
            margin-bottom: 30px;
            border-radius: 12px; /* rounded-xl */
            overflow: hidden;
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1); /* border-white/10 */
            position: relative;
        }
        .banner-img {
            width: 100%;
            height: auto;
            min-height: 100%;
            display: block;
            /* Center the image vertically if it's too tall */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
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
            white-space: pre-wrap;
            text-align: left;
        }
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
    @if($entry->banner_path)
        <div class="banner-container">
            <img src="{{ public_path('storage/' . $entry->banner_path) }}" class="banner-img">
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
        {{ $entry->content }}
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