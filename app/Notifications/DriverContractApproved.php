<?php

namespace App\Notifications;

use App\Notifications\Channels\MoviderSmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverContractApproved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', MoviderSmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Driver Contract Approved')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('We are pleased to inform you that your driver application has been successfully reviewed and approved.')
            ->line('Your contract is now active, and you may officially begin your duties as part of our team.')
            ->line('Please log in to the mobile application using your registered account to view important details.')
            ->line('We look forward to working with you and wish you a safe and successful journey ahead.');
    }

    public function toMovider(object $notifiable): string
    {
        return "Hi {$notifiable->name}, your driver application has been approved and your contract is now active. Please log in to the mobile app to start your duties.";
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
