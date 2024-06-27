<?php

namespace App\Domain\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class AccountLockedNotification extends Notification implements ShouldQueue
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
            ->subject(Lang::get('Account Locked Notification'))
            ->line(Lang::get('This email is to inform you that multiple unsuccessful login attempts have been detected on your account, reaching the maximum limit set for security purposes.'))
            ->line(Lang::get('If you have any concerns or need assistance, please contact System Administrator.'))
            ->line(Lang::get('Thank you for your attention to this matter.'));

    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
