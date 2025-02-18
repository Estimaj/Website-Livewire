<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class ContactFormSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly array $formData
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'telegram'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->view(
                'emails.contact-form-submitted',
                ['data' => $this->formData]
            )
            ->subject('New Contact Form Submission');
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable)
    {
        $fullName = "{$this->formData['firstName']} {$this->formData['lastName']}";

        return TelegramMessage::create()
            ->content("🔔 New Contact Form Submission\n\n" .
                "From: {$fullName}\n" .
                "Email: {$this->formData['email']}\n" .
                "Phone: {$this->formData['phone']}\n\n" .
                "Message:\n{$this->formData['message']}");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->formData;
    }
}
