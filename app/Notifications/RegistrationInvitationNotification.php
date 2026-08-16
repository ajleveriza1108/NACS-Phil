<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $registrationUrl,
        public readonly string $accountLabel,
        public readonly int $expiresInHours,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Complete your NACS-Phil account registration')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An authorized NACS-Phil administrator prepared your '.$this->accountLabel.'.')
            ->line('Use the secure link below to create your own strong password. A 6-digit verification code will then be sent to this same email address as the final registration step.')
            ->action('Complete registration', $this->registrationUrl)
            ->line('This invitation expires in '.$this->expiresInHours.' hours.')
            ->line('If you were not expecting this invitation, do not use the link and contact the school.');
    }
}
