<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StreakReminder extends Notification
{
    use Queueable;

    public $streak;

    /**
     * Create a new notification instance.
     *
     * @param  int  $streak
     * @return void
     */
    public function __construct($streak)
    {
        $this->streak = $streak;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $settings = $notifiable->settings ?? [];
        $streakAlerts = $settings['streak_alerts'] ?? false;

        if (!$streakAlerts) {
            return [];
        }

        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => "Don't break your {$this->streak}-day streak! Write a quick reflection before midnight.",
            'url' => route('entries.create', absolute: false),
        ];
    }
}
