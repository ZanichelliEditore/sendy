<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackChannel;
use Illuminate\Notifications\Slack\SlackMessage;

class SlackNotification extends Notification
{

    private string $notificationText;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $notificationText)
    {
        $this->notificationText = $notificationText;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [SlackChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Slack\SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)->to(env('SLACK_CHANNEL_NAME'))
            ->text($this->notificationText);
    }
}
