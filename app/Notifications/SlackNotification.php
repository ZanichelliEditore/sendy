<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class SlackNotification extends Notification
{

    /**
     * Get the notification channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->content('sono brutto')->to('#prove');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
