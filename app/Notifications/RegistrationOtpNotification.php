<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        public readonly string $verificationUrl,
        public readonly int $expiresInMinutes,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your NACS-Phil email verification code')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your NACS-Phil registration verification code is: '.$this->code)
            ->line('The code expires in '.$this->expiresInMinutes.' minutes and can be used only for this registration.')
            ->action('Enter verification code', $this->verificationUrl)
            ->line('Never share this code with anyone.');
    }
}
