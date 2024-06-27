<?php

namespace App\Domain\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Account Password Changed Notification'))
            ->line(Lang::get('This email is to confirm that your account password has been successfully changed.'))
            ->line(Lang::get('If you did not initiate this change or believe your account may be compromised, please contact our support team immediately for assistance.'))
            ->line(Lang::get('Thank you for your attention to this matter.'));
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
