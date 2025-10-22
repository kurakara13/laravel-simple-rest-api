<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminUserCreatedNotification extends Notification
{
    use Queueable;

    protected $userName;
    protected $userEmail;

    /**
     * Create a new notification instance.
     */
    public function __construct($userName, $userEmail)
    {
        $this->userName = $userName;
        $this->userEmail = $userEmail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New User Registration')
            ->greeting('Hello Administrator,')
            ->line('A new user has registered on the platform.')
            ->line('User Details:')
            ->line('Name: ' . $this->userName)
            ->line('Email: ' . $this->userEmail)
            ->line('Please review the new user account.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
