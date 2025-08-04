<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class StreakReminder extends Notification implements ShouldQueue
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
        $settings = $notifiable->settings;
        $receivePush = $settings && isset($settings['receive_push']) ? $settings['receive_push'] : false;

        return $receivePush ? [WebPushChannel::class, 'database'] : ['database'];
    }

    /**
     * Get the web push representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \NotificationChannels\WebPush\WebPushMessage
     */
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Streak Reminder!')
            ->body("Your {$this->streak}-day journaling streak ends tonight. Write now!")
            ->icon('/icon.png')
            ->data(['url' => route('entries.create')]);
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
            'message' => "Your {$this->streak}-day streak ends tonight. Write now!",
            'url' => route('entries.create')
        ];
    }

    /**
     * This method ensures the notification is dispatched after the database transaction is committed.
     * The framework automatically looks for and calls this method if the notification is dispatched
     * within a database transaction.
     *
     * @return void
     */
    public function afterCommit()
    {
        // No code is needed here. The mere existence of this method tells the framework
        // to wait for the transaction to complete before queuing the notification.
    }
}
