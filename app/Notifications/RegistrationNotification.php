<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RegistrationNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
                    ->line('Congratulations, you have successfully registered with us!')
                    ->action('Check them off here', route('dashboard'))
                    ->line('Hope you enjoy');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
