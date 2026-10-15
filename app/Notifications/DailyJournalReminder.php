<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DailyJournalReminder extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $settings = $notifiable->settings ?? [];
        $dailyReminder = $settings['daily_reminder'] ?? false;

        if (!$dailyReminder) {
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
            'message' => "Time to write in your journal! Start your day with a reflection.",
            'url' => route('entries.create')
        ];
    }
}
