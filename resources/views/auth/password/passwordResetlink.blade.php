<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
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
    style="margin: 0; padding: 0; background: linear-gradient(to bottom right, hsl(224, 71%, 4%), hsl(224, 65%, 5%)); font-family: 'Segoe UI', sans-serif; color: #ffffff;">

    <div style="
        max-width: 600px;
        margin: 60px auto;
        padding: 40px;
        border-radius: 12px;
        background-color: #060b16;
        background-image:
            radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px),
            linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 20px 20px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
    ">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="margin: 0; font-size: 38px; font-weight: bold; color: #ffffff;">

                Lumina
            </h1>
            <p style="margin: 10px 0 0 0; font-size: 17px; color: #b3b3b3;">
                Your trusted platform for journaling, reflection, and personal growth.
            </p>
        </div>

        <!-- Title -->
        <h2 style="font-size: 22px; margin-bottom: 20px; color: #ffffff; text-align: left;">
            Reset Your Password
        </h2>

        <!-- Intro -->
        <p style="font-size: 15px; line-height: 1.6; margin-bottom: 30px; text-align: left;">
            Hey {{ $name }},
            <br><br>
            We received a request to reset your password. Click the button below to proceed.
        </p>

        <!-- Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" class="button-hover" style="
                display: inline-block;
                padding: 14px 32px;
                background-color: #ffffff;
                color: #000000;
                text-decoration: none;
                font-weight: 600;
                font-size: 15px;
                border-radius: 8px;
            " target="_blank">
                Reset Password
            </a>
        </div>

        <p style="font-size: 14px; color: #aaaaaa; text-align: left; margin-bottom: 20px;">
            This password reset link will expire in
            {{ config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') }} minutes.
        </p>

        <!-- Warning -->
        <p style="font-size: 13px; color: #aaaaaa; text-align: left; margin-bottom: 30px;">
            If you didn’t request this, please secure your account by changing your password immediately.
        </p>

        <!-- Footer -->
        <p style="font-size: 12px; color: #666666; text-align: center; margin-top: 40px;">
            — The {{ config('app.name') }} Team
        </p>
    </div>
</body>

</html>