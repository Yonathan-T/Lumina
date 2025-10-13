<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: #000000;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            color: white;
            font-size: 28px;
            font-weight: 600;
        }

        .header p {
            margin: 10px 0 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .message {
            font-size: 15px;
            color: #4b5563;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        .stats-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }

        .stat-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }

        .download-button {
            display: inline-block;
            background: #000000;
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
        }

        .download-button:hover {
            transform: translateY(-2px);
            background: #1a1a1a;
        }

        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .warning-box p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
        }

        .footer {
            background: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 13px;
        }

        .footer a {
            color: #000000;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Your Export is Ready!</h1>
            <p>Your journal data has been prepared</p>
        </div>

        <div class="content">
            <div class="greeting">
                Hey {{ $userName }}! 👋
            </div>

            <div class="message">
                Great news! Your Lumina journal export is ready to download. We've packaged all your entries into a
                beautiful JSON format that's easy to use, import elsewhere, or visualize.
            </div>

            <div class="stats-box">
                <div class="stat-row">
                    <span class="stat-label">📝 Total Entries</span>
                    <span class="stat-value">{{ $totalEntries }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">📅 Exported On</span>
                    <span class="stat-value">{{ $exportedAt }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">⏰ Link Expires</span>
                    <span class="stat-value">{{ $expiresAt }}</span>
                </div>
            </div>

            <center>
                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                    style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                    <tr>
                        <td align="center">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                <tr>
                                    <td style="border-radius: 8px; background: #000000;">
                                        <a href="{{ $downloadUrl }}" download
                                            style="display: inline-block; background: #000000; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 14px 32px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);">
                                            📥 Download Export (JSON)
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>

            <div style="margin-top: 15px; text-align: center;">
                <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                    <strong>Button not working?</strong> Right-click the button above and select "Copy Link Address",
                    then paste it in your browser.
                </p>
            </div>

            <div class="warning-box">
                <p>
                    <strong>⚠️ Security Notice:</strong> This download link will expire in 24 hours for your security.
                    The file contains all your journal entries, so keep it safe!
                </p>
            </div>

            <div class="message">
                <strong>What's in the export?</strong><br>
                Your export includes all your journal entries with titles, content, tags, mood data, and timestamps in
                JSON format. Perfect for backups, data portability, or creating visualizations!
            </div>
        </div>

        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong> - Your AI-Powered Journal</p>
            <p>Questions? Reply to this email or visit our <a href="{{ config('app.url') }}">support page</a></p>
            <p style="margin-top: 20px; font-size: 12px; color: #9ca3af;">
                You received this email because you requested a data export from your {{ config('app.name') }} account.
            </p>
        </div>
    </div>
</body>

</html>