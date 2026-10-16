<?php

namespace App\Notifications;

use App\Models\Blog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBlogNotification extends Notification
{
    use Queueable;

    public $blog;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Blog  $blog
     * @return void
     */
    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
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
        $blogUpdates = $settings['blog_updates'] ?? false;

        if (!$blogUpdates) {
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
            'message' => "New article: {$this->blog->title}",
            'url' => route('blogs.index', absolute: false),
            'blog_id' => $this->blog->id,
        ];
    }
}
