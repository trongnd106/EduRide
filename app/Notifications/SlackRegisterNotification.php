<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SlackRegisterNotification extends Notification
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        $email = $this->data['email'];
        $userName = $this->data['full_name'];
        $userPhone = $this->data['user_phone'];

        $blockContent = "*User name:*" . $userName . "\n\n";
        $blockContent .= "*User email contact:* " . $email . "\n\n";
        $blockContent .= "*User phone:* " . $userPhone . "\n\n";

        return (new SlackMessage())
            ->from('Incoming-webhook')
            ->success()
            ->content('New user registered!')
            ->attachment(function ($attachment) use ($blockContent) {
                $attachment
                    ->content($blockContent)
                    ->markdown(['text']);
            });
    }
}
