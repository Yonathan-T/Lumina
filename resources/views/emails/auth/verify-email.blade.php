<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Verify Your Email Address</title>
    <!-- [if mso]>
    <style type="text/css">
        .button-hover:hover {
            background-color: #f8f0f0 !important;
            color: #000000 !important;
        }
    </style>
    <![endif]-->
    <style type="text/css">
        .button-hover:hover {
            background-color: #f8f0f0 !important;
        }
    </style>
</head>

<body
    style="margin: 0; padding: 0; background: linear-gradient(to bottom right, hsl(224, 71%, 4%), hsl(224, 65%, 5%)); font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif; color: #ffffff;">

    <div style="
        max-width: 600px;
        margin: 50px auto;
        padding: 40px;
        border-radius: 16px;
        background-color: #060b16;
        background-image:
            radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px),
            linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 20px 20px;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
    ">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="margin: 0; font-size: 36px; font-weight: bold; color: #ffffff; letter-spacing: 0.5px;">
                Lumina
            </h1>
            <p style="margin: 8px 0 0 0; font-size: 15px; color: #b3b3b3;">
                Your sanctuary for reflection, journaling, and mindful growth.
            </p>
        </div>

        <!-- Title -->
        <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: #ffffff; text-align: left;">
            Verify Your Email Address
        </h2>

        <!-- Intro -->
        <p style="font-size: 15px; line-height: 1.6; margin-bottom: 25px; color: #d1d5db; text-align: left;">
            Hello {{ $name }},
            <br><br>
            Welcome to Lumina! Please click the button below to verify your email address and activate your account.
        </p>

        <!-- CTA Button -->
        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $url }}" class="button-hover" style="
                display: inline-block;
                padding: 14px 36px;
                background-color: #ffffff;
                color: #000000;
                text-decoration: none;
                font-weight: 600;
                font-size: 15px;
                border-radius: 10px;
                box-shadow: 0 4px 14px rgba(255, 255, 255, 0.15);
            " target="_blank">
                Verify Email Address
            </a>
        </div>

        <!-- Fallback Raw Link -->
        <p style="font-size: 13px; color: #9ca3af; text-align: left; line-height: 1.5; margin-bottom: 24px; word-break: break-all;">
            If the button doesn't work, copy and paste this link into your browser:
            <br>
            <a href="{{ $url }}" style="color: #60a5fa; text-decoration: underline;">{{ $url }}</a>
        </p>

        <!-- Deliverability / Spam Warning Box -->
        <div style="margin-top: 25px; padding: 14px 18px; border-radius: 10px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1);">
            <p style="margin: 0; font-size: 13px; color: #d1d5db; line-height: 1.5;">
                <strong style="color: #facc15;">📬 Inbox Tip:</strong> If this email arrived in your spam or junk folder, please mark it as <em>"Not Spam"</em> so your account exports, reminders, and summaries arrive reliably in your inbox.
            </p>
        </div>

        <!-- Security / Expiry -->
        <p style="font-size: 13px; color: #6b7280; text-align: left; margin-top: 24px; margin-bottom: 0;">
            This link is secure and will expire in 60 minutes. If you did not create an account on Lumina, you can safely ignore this email.
        </p>

        <!-- Footer -->
        <p style="font-size: 12px; color: #4b5563; text-align: center; margin-top: 35px; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 20px;">
            — The {{ config('app.name') }} Team
        </p>
    </div>
</body>

</html>
