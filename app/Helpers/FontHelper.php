<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class FontHelper
{
    public static function getUserFont()
    {
        $user = Auth::user();
        $settings = $user->settings ?? [];
        return $settings['entry_font'] ?? 'inter';
    }

    public static function getFontClass()
    {
        $font = self::getUserFont();
        $fontMap = [
            'inter' => 'font-inter',
            'poppins' => 'font-poppins',
            'ubuntu' => 'font-ubuntu',
            'playfair' => 'font-playfair',
            'lora' => 'font-lora',
            'crimson' => 'font-crimson',
            'merriweather' => 'font-merriweather',
            'caveat' => 'font-caveat',
            'dancing' => 'font-dancing',
            'jetbrains' => 'font-jetbrains',
        ];
        return $fontMap[$font] ?? 'font-inter';
    }

    public static function getFontSize()
    {
        $user = Auth::user();
        $settings = $user->settings ?? [];
        return $settings['entry_font_size'] ?? 16;
    }
}