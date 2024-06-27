<?php

namespace App\Domain\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class AccountSuspendedNotification extends Notification implements ShouldQueue
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
            ->subject(Lang::get('Account Suspended Notification'))
            ->line(Lang::get('This email is to inform you that, your Account has been suspended. As a result, all roles and permissions previously granted to your account have been revoked.'))
            ->line(Lang::get('Please refrain from attempting to access the system until further notice.'))
            ->line(Lang::get('If you have any concerns or need assistance, please contact System Administrator.'))
            ->line(Lang::get('Thank you for your attention to this matter.'));
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
