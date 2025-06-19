<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class FcmNotification extends Notification
{
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData([
                'title' => 'Notification Title',
                'body' => $this->message,
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            // Convert the notification to an array if needed.
        ];
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }
}
